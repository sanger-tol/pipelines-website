---
title: Template Upgrade Instructions
subtitle: Instructions for upgrading the pipeline nf-core template
---

We strive to remain align with the latest nf-core template.
This minimises the risk of pipelines becoming incompatible with nf-core modules
and nf-core tools.

First read the [nf-core overview page](https://nf-co.re/docs/tutorials/sync/overview)
to get a sense of what the template update is.

## Template synchronisation

When a new version of nf-core tools is released,
one of our Nextflow "admins" ([@gq1](https://github.com/gq1),
[@muffato](https://github.com/muffato), [@prototaxites](https://github.com/prototaxites))
needs to go to [GitHub](https://github.com/sanger-tol/pipelines-website/actions/workflows/sync_template.yml)
and trigger the following workflow.

1. Click on the "Run workflow" drop-down menu
   <img src="/assets/img/template-upgrade-dropdown-light.png#gh-light-mode-only">
   <img src="/assets/img/template-upgrade-dropdown-dark.png#gh-dark-mode-only">
2. Click on the "Run workflow" button
   <img src="/assets/img/template-upgrade-run-light.png#gh-light-mode-only">
   <img src="/assets/img/template-upgrade-run-dark.png#gh-dark-mode-only">

By default, it runs on all pipelines registered on [this website](/pipelines) ("all")
but you can specify a pipeline name (without the "sanger-tol" prefix),
and with the latest nf-core version (empty field) but you can specify a version.

This will trigger a GitHub action that will run the `nf-core pipelines sync` command
for each pipeline and:

- push the `TEMPLATE` branch
- push a branch named `nf-core-template-merge-${VERSION}` initially on the same commit as `TEMPLATE`
- open a pull-request to merge `nf-core-template-merge-${VERSION}` into `dev` named "Important! Template update for nf-core/tools $VERSION"

## Pull-request review

If there are no merge conflicts on the PR, then that's great!
Review the changes and merge the pull-request.

However, it is quite rare.
Most often there will be some conflicts.
If you're lucky, GitHub will have a button for resolving the conflicts.
Open it and solve the conflicts in GitHub.
This will commit to the `nf-core-template-merge-${VERSION}` branch.
Then let the tests run, and if everything passes,
you're good to approve the pull-request.

Sometimes, the conflicts are too large and can't be solved from GitHub.
You will have to go to your clone, update it, and checkout the `nf-core-template-merge-${VERSION}` branch.
Run `git merge dev`. This will fail and show the same conflicts as GitHub.
Solve all the conflicts, `git commit`, `git push`, and there you go.



You will probably get a tonne of log messages telling you about merge conflicts:

```console
$ git pull upstream TEMPLATE

remote: Enumerating objects: 33, done.
remote: Counting objects: 100% (33/33), done.
remote: Compressing objects: 100% (18/18), done.
remote: Total 33 (delta 15), reused 33 (delta 15), pack-reused 0
Unpacking objects: 100% (33/33), done.
From github.com:nf-core/rnaseq
 * branch            TEMPLATE   -> FETCH_HEAD
   55d617e..2d7814a  TEMPLATE   -> upstream/TEMPLATE
Auto-merging nextflow.config
CONFLICT (content): Merge conflict in nextflow.config
Auto-merging main.nf
CONFLICT (content): Merge conflict in main.nf
Auto-merging environment.yml
CONFLICT (content): Merge conflict in environment.yml
...
```

If you look at the current status, you will see the files that have merge conflicts that need resolving _(Unmerged paths)_:

```console
$ git status

On branch merging-template-updates
You have unmerged paths.
  (fix conflicts and run "git commit")
  (use "git merge --abort" to abort the merge)

Changes to be committed:

    modified:   .github/ISSUE_TEMPLATE/bug_report.md
    modified:   .github/ISSUE_TEMPLATE/feature_request.md
    modified:   .github/markdownlint.yml
    modified:   .gitignore
    new file:   bin/markdown_to_html.py
    deleted:    bin/markdown_to_html.r
    deleted:    conf/awsbatch.config

Unmerged paths:
  (use "git add/rm <file>..." as appropriate to mark resolution)

    both modified:   .github/CONTRIBUTING.md
    both modified:   .github/PULL_REQUEST_TEMPLATE.md
    both added:      .github/workflows/branch.yml
    both added:      .github/workflows/ci.yml
    both added:      .github/workflows/linting.yml
    deleted by them: .travis.yml
    both modified:   CHANGELOG.md
    both modified:   CODE_OF_CONDUCT.md
    both modified:   Dockerfile
    both modified:   README.md
    both modified:   assets/multiqc_config.yaml
    both modified:   bin/scrape_software_versions.py
    both modified:   conf/base.config
    both modified:   conf/igenomes.config
    both modified:   conf/test.config
    both modified:   docs/output.md
    both modified:   docs/usage.md
    both modified:   environment.yml
    both modified:   main.nf
    both modified:   nextflow.config
```

You now need to go through each of these files to resolve every merge conflict.
Most code editors have tools to help with this, for example [VSCode](https://code.visualstudio.com/docs/editor/versioncontrol#_merge-conflicts) have built-in support.

Be careful when resolving conflicts.
Most of the time you will want to use the version from the `TEMPLATE` branch,
but be aware that some of this new template code may need to be customised by your pipeline.
In other words, you may need to manually combine the two versions in to one new code block.

If you have any doubts, ask for help on the nf-core Slack.

### Pushing the resolved changes to your fork

When all merge conflicts have been resolved and all files are staged, you can commit and push these changes as with any other new code:

```bash
git commit -m "Merged changes from nf-core template"
git push --set-upstream origin merging-template-updates
```

### Merging to the nf-core repository

Once the changes are on your fork, you can make a pull request to the main nf-core repository for the pipeline.
This should be reviewed and merged as usual.
You should see in the commit history on the PR that there is a commit by the @nf-core-bot user, with the same commit hash found in the automated `TEMPLATE` PR.

Once your fork is merged, the automated PR will also show as merged and will close automatically.


> Check the tool release post on the [nf-core blob](https://nf-co.re/blog).
> It often has instructions and tips to handle the merge conflicts of this particular version.

Note: this page contains content copied from the [nf-core website](https://nf-co.re/docs/tutorials/sync/merging_automated_prs).

