---
title: Testing Pipelines
subtitle: How we test our pipelines
---

## nf-core standards

The nf-core template comes with a "profile" named `test` and one named `test_full`.

The `test` profile is meant to define minimal input data and parameters so that the pipeline can run in
5-10 minutes and within the resources available on GitHub (4 CPUs, 16 GB RAM).
The goal is to check that the pipeline can successfully run and
generate some non-empty files.
Given the time constraint, the input data are typically a very small region of a genome,
sequencing data are limited to 1,000-10,000 reads, etc.

The `test_full` profile is meant to define input data and parameters that represent
a complete dataset. Such runs will typically take hours to complete on the farm.  
Neither of those profiles is expected to cover 100% of the pipeline features.

[nf-test](https://www.nf-test.com/) is gradually making its way through nf-core.
Presently, a minority of our pipelines implement nf-test, but we expect this to
change as nf-core define a standard way of running nf-test for pipelines in a
future version.

## Test data

The `test` profile should be usable by anyone anywhere.
This means that its input data should be on publicly accessible web servers.
We use Sanger's Ceph S3

1. Deposit your data under `/nfs/treeoflife-01/resources/nextflow/`.
2. Ask [@gq1](https://github.com/gq1) or [@muffato](https://github.com/muffato) to synchronise the directory.
3. Access your data throuh `https://tolit.cog.sanger.ac.uk/test-data/` (same sub-path as on disk)

We currently do not set such requirements for the inputs of the `test_full` profile.

## Continuous Integration (CI)

The nf-core template includes a Continuous Integration workflow that runs the `test` profile.
GitHub will run the test in pull-requests and will **block** the merge until the test
passes.
The `test` profile needs to give you reasonable confidence the changes don't
break the pipeline.  
Note: you may need to add steps to download and configure input databases that are
required by the pipeline. The file is `.github/workflows/ci.yml`.

Our policy is to also run the `test_full` profile but only after the pull-requests are
merged due to the compute costs.
For that purpose, we provide a CI workflow [`sanger_test_full.yml`](https://github.com/sanger-tol/pipelines-website/blob/main/sanger_test_full.yml)
that you can add to your `.github/workflows/` directory.
This workflow will trigger a run of the `test_full` profile on the farm, via
Nextflow Tower (Seqera Platform).
The outputs of the `test_full` profile on the `main` branch are also
used to populate the "Results" tab of your pipeline on this website.

Finally, we also provide the equivalent CI workflow for the `test` profile:
[`sanger_test.yml`](https://github.com/sanger-tol/pipelines-website/blob/main/sanger_test.yml).
This is mostly used for debugging `test` profile failures.
