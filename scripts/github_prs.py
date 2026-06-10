#!/usr/bin/env python3

import json
import os
import subprocess
import urllib.request
from collections import Counter
from datetime import datetime, timedelta, timezone

ORG = os.environ.get("GITHUB_ORG", "sanger-tol")
SLACK_WEBHOOK_URL = os.environ["SLACK_WEBHOOK_URL"]
WEEKS = int(os.environ.get("WEEKS", "4"))

WINDOW_START = datetime.now(timezone.utc) - timedelta(weeks=WEEKS)
WINDOW_DATE = WINDOW_START.date().isoformat()

QUERY = """
query($searchQuery: String!, $cursor: String) {
  search(
    query: $searchQuery
    type: ISSUE
    first: 100
    after: $cursor
  ) {
    pageInfo {
      hasNextPage
      endCursor
    }

    nodes {
      ... on PullRequest {
        number
        createdAt
        closedAt

        repository {
          name
        }

        author {
          login
          ... on User {
            name
          }
        }

        reviews(first: 100) {
          nodes {
            state
            author {
              ... on User {
                name
              }
            }
          }
        }
      }
    }
  }
}
"""


def parse_ts(value):
    if not value:
        return None

    return datetime.fromisoformat(
        value.replace("Z", "+00:00")
    )


def graphql_search(cursor=None):
    search_query = (
        f"org:{ORG} is:pr updated:>={WINDOW_DATE}"
    )

    cmd = [
        "gh",
        "api",
        "graphql",
        "-f",
        f"query={QUERY}",
        "-F",
        f"searchQuery={search_query}",
    ]

    if cursor:
        cmd.extend(["-F", f"cursor={cursor}"])

    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            check=True,
        )
    except subprocess.CalledProcessError as e:
        print("COMMAND:")
        print(" ".join(cmd))
        print("\nSTDOUT:")
        print(e.stdout)
        print("\nSTDERR:")
        print(e.stderr)
        raise

    payload = json.loads(result.stdout)

    if "errors" in payload:
        raise RuntimeError(
            json.dumps(payload["errors"], indent=2)
        )

    return payload["data"]["search"]


def iter_prs():
    cursor = None

    while True:
        page = graphql_search(cursor)

        for pr in page["nodes"]:
            yield pr

        if not page["pageInfo"]["hasNextPage"]:
            break

        cursor = page["pageInfo"]["endCursor"]


def build_report():
    opened = 0
    closed = 0

    creator_counts = Counter()
    reviewer_counts = Counter()
    repo_activity = Counter()

    for pr in iter_prs():
        created_at = parse_ts(pr["createdAt"])
        closed_at = parse_ts(pr["closedAt"])
        repo = pr["repository"]["name"]

        if created_at and created_at >= WINDOW_START:
            opened += 1

            author = pr.get("author")
            if author and author.get("name"):
                creator_counts[author["name"]] += 1

        if closed_at and closed_at >= WINDOW_START:
            closed += 1

        if created_at >= WINDOW_START:
            repo_activity[repo] += 1

        if closed_at and closed_at >= WINDOW_START:
            repo_activity[repo] += 1

        reviewers_for_pr = set()

        for review in pr["reviews"]["nodes"]:
            author = review.get("author")

            if not author:
                continue

            login = author.get("name")

            if login:
                reviewers_for_pr.add(login)

        for login in reviewers_for_pr:
            reviewer_counts[login] += 1

        repo_activity[repo] += len(reviewers_for_pr)

    lines = [
        f":olympics: *GitHub PR Report (last {WEEKS} week{'' if WEEKS == 1 else 's'})*",
        "",
        f"Opened: {opened}",
        f"Closed: {closed}",
        "",
        ":artist: *Top PR Creators*",
    ]

    for rank, (user, count) in enumerate(
        creator_counts.most_common(10),
        start=1,
    ):
        lines.append(
            f"{rank}. {user} ({count})"
        )

    lines.extend(
        [
            "",
            ":judge: *Top Reviewers*",
        ]
    )

    for rank, (user, count) in enumerate(
        reviewer_counts.most_common(10),
        start=1,
    ):
        lines.append(
            f"{rank}. {user} ({count})"
        )

    lines.extend(
        [
            "",
            ":books: *Most active repos*",
        ]
    )

    for rank, (repo, count) in enumerate(
        repo_activity.most_common(10),
        start=1,
    ):
        lines.append(
            f"{rank}. {repo} ({count})"
        )

    return "\n".join(lines)


def post_to_slack(message):
    payload = json.dumps(
        {"text": message}
    ).encode()

    request = urllib.request.Request(
        SLACK_WEBHOOK_URL,
        data=payload,
        headers={
            "Content-Type": "application/json"
        },
    )

    with urllib.request.urlopen(request):
        pass


def main():
    report = build_report()

    print(report)
    post_to_slack(report)


if __name__ == "__main__":
    main()
