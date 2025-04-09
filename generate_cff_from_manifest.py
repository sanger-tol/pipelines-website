#!/software/treeoflife/conda/users/envs/tol/nf-core_3.2/bin/python3

"""Code to deal with pipeline RO (Research Object) Crates the sanger-tol way"""

import datetime
import json
import logging
import operator
import os
import sys
from pathlib import Path

import rich_click as click
from rich.progress import BarColumn, Progress
import ruamel.yaml

from nf_core.pipelines.lint_utils import dump_yaml_with_prettier
from nf_core.utils import Pipeline

log = logging.getLogger(__name__)

message = "If you use this software, please cite it using the metadata from this file and all references from CITATIONS.md ."

def get_pipeline(path):
    pipeline_obj = Pipeline(path)
    pipeline_obj._load()
    return pipeline_obj

def get_contributors(pipeline_obj):
    if "manifest.contributors" not in pipeline_obj.nf_config:
        log.error("No contributors field in manifest of nextflow.config")
        return

    # Grab the contributor list and convert to JSON 
    contributors_str = pipeline_obj.nf_config["manifest.contributors"]
    log.debug("manifest.contributors", contributors_str)
    # JSON uses double quotes, not single quotes
    contributors_str = contributors_str.replace("'", '"')
    for key in ["name", "affiliation", "github", "contribution", "orcid", "email"]:
        # All dictionary keys need to be quoted
        contributors_str = contributors_str.replace(f"{key}:", f"\"{key}\":")
    # Use curly brackes for dictionaries
    contributors_str = contributors_str.replace("], [", "}, {").replace("[[", "[{").replace("]]", "}]")
    contributors = json.loads(contributors_str)

    # Using a progress bar because parsing the git log could be slow
    progress_bar = Progress(
        "[bold blue]{task.description}",
        BarColumn(bar_width=None),
        "[magenta]{task.completed} of {task.total}[reset] » [bold yellow]{task.fields[name]}",
        transient=True,
        disable=os.environ.get("HIDE_PROGRESS", None) is not None,
    )
    with progress_bar:
        bump_progress = progress_bar.add_task(
            "Searching for author emails", total=len(contributors), name=""
        )

        for author in contributors:

            if "name" not in author:
                log.error(f"No name  field for author: {author}")
                sys.exit(1)

            progress_bar.update(bump_progress, advance=1, name=author["name"])

            # Fill in the email from the git history (if missing)
            if "email" not in author or not author["email"].strip():
                # get email from git log
                name = author["name"].split()[0].replace(",", "")
                email = pipeline_obj.repo.git.log(f"--author={name}", "--pretty=format:%ae", "-1")
                if email:
                    author["email"] = email

            # Fix the ORCID URL
            if "orcid" in author and author["orcid"]:
                orcid = author["orcid"]
                if not orcid.startswith("http"):
                    author["orcid"] = "https://orcid.org/" + orcid

            # Fix the GitHub URL
            if "github" in author:
                if author["github"].startswith("@"):
                    author["github"] = "https://github.com/" + author["github"][1:]
                elif not author["github"].startswith("http"):
                    author["github"] = "https://github.com/" + author["github"]

    return contributors

# Only update the dictionary if there's a value
def set_if_set(d, k, v):
    if v is not None:
        sv = v.strip()
        if sv:
            d[k] = sv

def find_release_name(pipeline_dir, version):
    changelog = pipeline_dir / "CHANGELOG.md"
    with changelog.open() as f:
        for l in f:
            if l.startswith("#") and version in l and ("2024" in l or "2025" in l):
                # We have a variety of line structures (and hyphen styles !)
## [[0.7.1](https://github.com/sanger-tol/blobtoolkit/releases/tag/0.7.1)] – Psyduck (patch 1) – [2025-03-29]
## [[0.7.0](https://github.com/sanger-tol/blobtoolkit/releases/tag/0.7.0)] – Psyduck – [2025-03-19]
## [1.2.2] - Ancient Destiny (H2)- [2025-01-30]
## [1.2.0] - Ancient Destiny - [2024-11-15]
## [[1.3.1](https://github.com/sanger-tol/curationpretext/releases/tag/1.3.1)] - UNSC Pillar-of-Autumn (H1) - [2025-04-02]
## [[1.3.0](https://github.com/sanger-tol/curationpretext/releases/tag/1.3.0)] - UNSC Pillar-of-Autumn - [2025-02-27]
## v0.7.0 - Raymond Carhart [08/03/2025]
## [1.1.0dev] - unnamed - [2025-03-31]
## [[1.0.1](https://github.com/sanger-tol/metagenomeassembly/releases/tag/1.0.1)] - Scarborough Fair (patch 1) - [2025-03-31]
                l = l.replace("–", "-")
                name = l.split("- ")[1].strip()
                return name

# Intro: https://citation-file-format.github.io/
# Schema: https://github.com/citation-file-format/citation-file-format/blob/main/schema-guide.md
def build_cff(pipeline_obj):
    pipeline_name = pipeline_obj.nf_config["manifest.name"]
    pipeline_version = pipeline_obj.nf_config["manifest.version"]
    release_name = find_release_name(pipeline_obj.wf_path, pipeline_version)

    content = {
        "cff-version": "1.2.0",
        "message": message,
        "type": "software",  # it's either that or "dataset"
        "repository-code": pipeline_obj.nf_config["manifest.homePage"],
        "url": f"https://pipelines.tol.sanger.ac.uk/{pipeline_name.split('/')[1]}",
        "license": "MIT",
        "title": f"{pipeline_name} v{pipeline_version}",
        "commit": pipeline_obj.repo.head.commit.hexsha,
        "version": pipeline_version,
        "date-released": datetime.date.today().isoformat(),
    }
    set_if_set(content, "doi", pipeline_obj.nf_config.get("manifest.doi"))
    if release_name:
        content["title"] = content["title"] + " - " + release_name
    contributors = get_contributors(pipeline_obj)
    authors = []
    for contributor in contributors:

        log.debug(f"Adding author: {author}")




        author = {}
        set_if_set(author, "affiliation", contributor.get("affiliation"))
        set_if_set(author, "orcid", contributor.get("orcid"))
        set_if_set(author, "email", contributor.get("email"))
        set_if_set(author, "website", contributor.get("github"))
        if "," in contributor["name"]:
            (family, given) = contributor["name"].split(",", 1)
        elif " " in contributor["name"]:
            (given, family) = contributor["name"].split(maxsplit=1)
        else:
            given = contributor["name"]
            family = None
        set_if_set(author, "given-names", given)
        set_if_set(author, "family-names", family)
        authors.append(author)
    authors.sort(key=operator.itemgetter("family-names"))
    content["authors"] = authors
    return content

@click.command()
@click.argument(
    "pipeline_dir",
    type=click.Path(exists=True),
    default=Path.cwd(),
    required=True,
    metavar="<pipeline directory>",
)
def cff(pipeline_dir):
    pipeline_obj = get_pipeline(pipeline_dir)
    content = build_cff(pipeline_obj)
    # dump_yaml_with_prettier expects to be run from the repository
    os.chdir(pipeline_dir)
    dump_yaml_with_prettier("CITATION.cff", content)

if __name__ == "__main__":
    cff()
