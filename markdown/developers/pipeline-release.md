---
title: Pipeline Release Instructions
subtitle: Instructions for releasing a sanger-tol pipeline
---

> This page is heavily inspired by the [nf-core release checklist](https://nf-co.re/docs/contributing/release_checklist).

## Branch model

First a reminder about how we use branches in sanger-tol.

`main` is the release branch. Releases can only happen from the `main` branch
and code present on the `main` branch _has_ to be released. `main` can be
updated by merging either the staging branch, `dev`, or the bugfix branch, `patch`.

`dev` is the staging branch, which accumulates new features before release.
Code on the `dev` branch should always pass tests and be functional (this is a
condition of our [review guidelines](/docs/contributing/review_checklist)).

Bugfix branches can be used to patch a released pipeline. The branch needs to
be named `patch`,
be created off `main` and merged into `main` for immediate release. There needs
to be extra caution when merging a bugfix branch into `main` as there isn't the
`dev` branch to act as a buffer.

The model implies that apart from bugfixes (patch number changes), new versions
can only come from the tip of the development tree.
If you have already released 1.0.0, 1.1.0, and 2.0.0,
then the next release will be either 2.1.0, 3.0.0, or a bugfix
(2.0.1, 1.1.1, or 1.0.1), although
the intent of the model is that once 2.0.0 is released, versions 1.\*
are not maintained any more.

## Integration with Zenodo

### DOI

The Zenodo record will be created automatically but we can't know the DOI until after the release !
We have to do a release first, wait to get the DOI from Zenodo, and then we can update all references
in the repository.

You have two strategies:

- Do a pre-release, e.g. 0.\*, get the DOI, and then release 1.0 with the DOI included.
- Release 1.0 without the DOI and include the DOI in 1.0.1 (or later).

### Author list and other metadata

Zenodo tries to infer the authors from the history of the code and certain files,
but it's imperfect.
The best way is to create a `CITATION.cff` file.

[Citation File Format](https://citation-file-format.github.io/) is a standard for
recording contributions.
The file lists all the authors, their affiliation, and their ORCID, and is loaded by Zenodo.

If you're based on the latest nf-core template,
the information should already be in the "manifest" found in `nextflow.config`.
We provide a script to automatically write `CITATION.cff` from the manifest.
Simply run:

```
/software/treeoflife/bin/generate_cff_from_manifest.py  # if you're at the root of the repository
/software/treeoflife/bin/generate_cff_from_manifest.py path/to/repository  # otherwise
```

The command will regenerate the `CITATION.cff` file in the repository.

Note that the version number, as defined in `nexflow.config` is copied into `CITATION.cff`.
If you adhere to the nf-core convention for versioning the `dev` branch, you'll have
the `dev` suffix in `CITATION.cff` too if you run the script from that branch.
For that reason, it is easier to run it at the last minute, right before merging `dev` into `main`.

### Update the record after release

Once you've made a release, a record is automatically created on Zenodo.
Tell [@muffato](https://github.com/muffato) or [@DLBPointon](https://github.com/DLBPointon) who can then do the following:

1. Check the release notes. Markdown tables are not converted by default. You may need to manually copy the rendered table from GitHub into the Zenodo editor.
2. Change the record type from "Software" to "Workflow".
3. Check that all authors are properly named (first name and last name identified), have an ORCiD, and have the Sanger affiliation – should be all good if you used a `CITATION.cff`.
4. Add the pipeline to the [sanger-tol Zenodo community](https://zenodo.org/communities/sanger-tol) – only needed for the first upload.
5. Check that the licence is correctly set to MIT – should be all good if you used a `CITATION.cff`.
6. In the "Software" section, link to GitHub URL, enter "Nextflow" as the language, and set the "Development Status" to "Active".

## Versioning

`nf-core pipelines bump-version` can automatically update the version in several locations.

As a reminder, you should be versioning your `dev` branch `${major_number}.${minor_number}dev`.
This is to ensure that users don't inadvertently take our `dev` branch as release-ready.
That means additional steps to remove the `dev` suffix right before merging into `main` / the release,
and adding it back after the pull-request.

### Nomenclature

About version numbers and release names:

1. The release _number_ is made of digits and dots **only**, and follows [Semantic Versioning](https://semver.org/).
   It is used as the git _tag_ too.
2. The release _name_ is whatever you want. It can be Harry Potter, Stargate, or Pokemon. Express your creativity! You only need a new name for major and minor releases. Patch releases reuse the last name with the addition of `(patch ${PATCH_NUMBER})`.
3. The release _title_ you enter on GitHub must be of the form `v${RELEASE_NUMBER} - ${RELEASE_NAME}`. No need to include the pipeline name there, especially as it is added by Zenodo later.
4. The release _description_ you enter on GitHub should be the same from what you have in the Changelog, e.g.:

```text
## [[${RELEASE_NUMBER}](https://github.com/sanger-tol/${PIPELINE_NAME}/releases/tag/${RELEASE_NUMBER})] - ${RELEASE_NAME} - [${RELEASE_DATE}]

*Summary of the release*

### Enhancements & fixes

- *List of what's changed. Indicate whether they're bug fixes, additions, or breaking changes*

### Parameters

| Old parameter | New parameter |
| ------------- | ------------- |
|               | --added       |
| --removed     |               |
| --old         | --new         |

> **NB:** Parameter has been **updated** if both old and new parameter information is present. </br> **NB:** Parameter has been **added** if just the new parameter information is present. </br> **NB:** Parameter has been **removed** if new parameter information isn't present.

### Software dependencies

Note, since the pipeline is using Nextflow DSL2, each process will be run with its own [Biocontainer](https://biocontainers.pro/#/registry). This means that on occasion it is entirely possible for the pipeline to be using different versions of the same tool. However, the overall software dependency changes compared to the last release have been listed below for reference.

| Dependency   | Old version | New version |
| ------------ | ----------- | ----------- |
| name_removed | 2.30.0      |             |
| name_added   |             | 5.4.3       |
| name_changed | 0.8.10      | 0.8.11      |

> **NB:** Dependency has been **updated** if both old and new version information is present. </br> **NB:** Dependency has been **added** if just the new version information is present. </br> **NB:** Dependency has been **removed** if version information isn't present.
```

Alternatively, GitHub can also generate release notes from the list of commits. Just make sure you have this at the top:

```text
## [[${RELEASE_NUMBER}](https://github.com/sanger-tol/${PIPELINE_NAME}/releases/tag/${RELEASE_NUMBER})] - ${RELEASE_NAME} - [${RELEASE_DATE}]

*Summary of the release*
```

## RO Crate

[Research Object Crates (RO-Crates)](https://www.researchobject.org/ro-crate/) are machine-readable,
standardised, files that include metadata about a software (workflow).

RO-Crates have got space to defined the authors of the software,
and nf-core can create/update the `ro-crate-metadata.json` file automatically.
However, it gets the list of authors **only** from the git history of `main.nf`.

Just like for `CITATION.cff`, we have a script to automatically regenerate
`ro-crate-metadata.json` from the pipeline manifest. Run:

```
/software/treeoflife/bin/generate_rocrate_from_manifest.py  # if you're at the root of the repository
/software/treeoflife/bin/generate_rocrate_from_manifest.py path/to/repository  # otherwise
```

Unfortunately, `nf-core pipelines bump-version` regenerates the file every time it is called.
Like `CITATION.cff`, it is easier to run it at the last minute, right before merging `dev` into `main`.

## Release steps

Follow the "Before you release" and "Steps to release" from the [nf-core release checklist](https://nf-co.re/docs/contributing/release_checklist) with the following adaptations:

1. Replace "nf-core" with "sanger-tol".
2. Since we develop directly off the sanger-tol repository, the version bump happens there, not "on your fork".
3. The "core team member" who can activate the integration with Zenodo is [@muffato](https://github.com/muffato).
4. We don't have Twitter integration.

## Upload to WorkflowHub

As part of an EBP and ERGA recommendation, we should deposit our workflows into [WorkflowHub](https://workflowhub.eu/programmes/37) too.
There isn't an automated way of doing that yet, so in the meantime we need to manually upload the releases.

In order to upload a record, you will need to create an account and ask to join one of our two teams (["Genome Assembly"](https://workflowhub.eu/projects/204) and ["Genome Analysis"](https://workflowhub.eu/projects/205)).
Instructions are on their website: <https://about.workflowhub.eu/docs/>
