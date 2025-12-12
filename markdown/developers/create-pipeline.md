---
title: Pipeline Creation Instructions
subtitle: Instructions for creating a sanger-tol pipeline
---

## nf-core instructions

This page is heavily inspired by the nf-core page [Adding a new pipeline](https://nf-co.re/docs/contributing/adding_pipelines).

## Create the pipeline

All pipelines _must_ use the [nf-core template](https://nf-co.re/docs/contributing/guidelines/requirements/use_the_template).
This is done by using the `nf-core pipelines create` command - see [the docs](https://nf-co.re/docs/nf-core-tools/pipelines/create) for detailed instructions.
This tool does lots of things for you: it gives you the correct file structure and boiler plate code
and also sets up the required `git` infrastructure for you to keep your pipeline in sync in the future.

The command has a user interface that will ask how you want to customise the template.
Answer these:

- _Choose pipeline type_: choose "custom"
- _GitHub organisation_: enter `sanger-tol`
- _Template features_: disable:
  - _Use reference genomes_. This is only relevant when dealing _exclusively_ with model organisms.
  - _Use multiqc_. **If** you're unsure whether you need it, or don't want to consider it a requirement of your pipeline (if you leave the option enabled, **every** template upgrade will try to update it).
  - _Use fastqc_. Same reasoning as _multiqc_ above.

There are other options such as the Microsoft Teams notifications that we don't use ourselves but are harmless to keep (and our users may find those options useful).

## Push to GitHub

Create an empty repository on GitHub for your new pipeline under <https://github.com/sanger-tol>.
Do this by:

1. going to the GitHub website,
2. clicking `+` then _New Repository_,
3. selecting "sanger-tol" as the Owner.

Make sure _not_ to initialise it with _any_ file, `README` or `LICENSE`: you just want an empty repository.
You already have these files generated from the nf-core template.

Leave the repository as "Public". We don't want to hide our pipelines, even when they're in progress.

Once created, copy the git URL and add this as a remote to your local git repository.
The `nf-core create` command will have initialised a git repository for you,
so all you need to do is add the remote:

```bash
## Add a remote called 'origin' - this is the default name for a primary remote
git remote add origin https://github.com/sanger-tol/PIPELINE_NAME.git
## Or the following if you have SSH keys configured on GitHub
git remote add origin git@github.com:sanger-tol/PIPELINE_NAME.git
```

The create command also generated the three standard nf-core branches (`master`, `dev` and `TEMPLATE`),
together with an initial commit which is shared between them.
This git structure is required for automatic template synchronisation in the future.

You first need to rename the `master` branch:

```bash
git branch -m master main
```

Then, you can push these new branches to the remote GitHub repository:

```bash
git push --all origin
```

You should now see the vanilla nf-core template and branches in the github.com web interface.

## GitHub configuration

Head up to your repository on GitHub and do the following.

In the About section on the right, click on the cog wheel and:

1. Set the URL to <https://pipelines.tol.sanger.ac.uk/$PIPELINE_NAME>.
2. Add the topics `pipeline` and `nextflow`. This is required to enable it on the pipelines website.
   - Most pipelines also have `workflow` and `genomics`.
3. Enter a description.

Then, ask [@muffato](https://github.com/muffato) or [@mcshane](https://github.com/mcshane) to:

1. Add the repository to the ["nextflow_all"](https://github.com/orgs/sanger-tol/teams/nextflow_all) team with the "write" permission.
2. Add the repository to the ["nextflow_admin"](https://github.com/orgs/sanger-tol/teams/nextflow_admin) team with the "admin" permission.
3. Double-check that you're part of the ["nextflow_all"](https://github.com/orgs/sanger-tol/teams/nextflow_all) team (and add you otherwise !).
4. Remove your individual access to the repository.
5. Allow your repository to access all the secrets from <https://github.com/organizations/sanger-tol/settings/secrets/actions>.

Finally, ask [@gq1](https://github.com/gq1) or [@muffato](https://github.com/muffato) to:

1. Refresh the list of pipelines by clicking the link at the bottom of the [pipelines page](/pipelines).
2. Set up the repository branch settings by selecting the pipeline and clicking "Fix data" on the [pipeline health page](/pipeline_health).

## Other bits

We're almost done. We now need to push some changes to the `main` branch to customise our pipeline a little further.

### Copyright

We licence our pipelines with the MIT license.
The MIT licence should already be in your repository, coming from the nf-core template, but we need to update the copyright statement to:

> Copyright (c) 2025 Genome Research Ltd.

(or whichever year we're in !).

### `main` vs `master` branch

Support for `main` is gradually coming in nf-core but we still need to change a few things:

- In `.github/workflows/ci.yml`, replace the occurrence of `master` with `main`.
- In `.github/workflows/linting.yml`, replace the two occurrences of `master` with `main`.

### Logo

To add the sanger-tol logo to your pipeline, edit `nextflow.config`

Add this at the end of the `help` dictionary (under `validation`):

```
        beforeText = """
-\033[2m----------------------------------------------------\033[0m-
\033[0;34m   _____                               \033[0;32m _______   \033[0;31m _\033[0m
\033[0;34m  / ____|                              \033[0;32m|__   __|  \033[0;31m| |\033[0m
\033[0;34m | (___   __ _ _ __   __ _  ___ _ __ \033[0m ___ \033[0;32m| |\033[0;33m ___ \033[0;31m| |\033[0m
\033[0;34m  \\___ \\ / _` | '_ \\ / _` |/ _ \\ '__|\033[0m|___|\033[0;32m| |\033[0;33m/ _ \\\033[0;31m| |\033[0m
\033[0;34m  ____) | (_| | | | | (_| |  __/ |        \033[0;32m| |\033[0;33m (_) \033[0;31m| |____\033[0m
\033[0;34m |_____/ \\__,_|_| |_|\\__, |\\___|_|        \033[0;32m|_|\033[0;33m\\___/\033[0;31m|______|\033[0m
\033[0;34m                      __/ |\033[0m
\033[0;34m                     |___/\033[0m
\033[0;35m  ${manifest.name} ${manifest.version}\033[0m
-\033[2m----------------------------------------------------\033[0m-
"""
        afterText = """${manifest.doi ? "\n* The pipeline\n" : ""}${manifest.doi.tokenize(",").collect { "    https://doi.org/${it.trim().replace('https://doi.org/','')}"}.join("\n")}${manifest.doi ? "\n" : ""}
* The nf-core framework
    https://doi.org/10.1038/s41587-020-0439-x
* Software dependencies
    https://github.com/sanger-tol/blobtoolkit/blob/main/CITATIONS.md
"""
```

And add another dictionary named `summary` at the end of the `validation` dictionary:

```
    summary {
        beforeText = validation.help.beforeText
        afterText = validation.help.afterText
    }
```

You should then see this in your terminal when running the pipeline:
<img src="/assets/img/developer-images/sanger-tol-logo-cli.png" alt="Sanger-tol logo rendered in a terminal">

### Zenodo

The repository needs to be integrated with Zenodo before making the first release.
Better to do it now before anyone forgets !
Ask [@muffato](https://github.com/muffato) to enable the Zenodo integration.
