---
title: Template Upgrade Instructions
subtitle: Instructions for upgrading the pipeline nf-core template
---

We strive to remain align with the latest nf-core template.
This minimises the risk of pipelines becoming incompatible with nf-core modules
and nf-core tools.

## Template synchronisation

When a new version of nf-core tools is released,
one of our Nextflow "admins" ([@gq1](https://github.com/gq1),
[@muffato](https://github.com/muffato), [@prototaxites](https://github.com/prototaxites))
needs to go to [GitHub](https://github.com/sanger-tol/pipelines-website/actions/workflows/sync_template.yml)
and trigger the following workflow.

1. Click on the "Run workflow" drop-down menu
   <img src="public_html/assets/img/logo/template-upgrade-dropdown-light.png#gh-light-mode-only">
   <img src="public_html/assets/img/logo/template-upgrade-dropdown-dark.png#gh-dark-mode-only">
2. Click on the "Run workflow" button
   <img src="public_html/assets/img/logo/template-upgrade-run-light.png#gh-light-mode-only">
   <img src="public_html/assets/img/logo/template-upgrade-run-dark.png#gh-dark-mode-only">

By default, it runs on all pipelines registered on [this website](/pipelines) ("all")
but you can specify a pipeline name (without the "sanger-tol" prefix),
and on the latest nf-core version (empty field) but you can specify a version.

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

