---
title: Genome After-Party pipeline standards
subtitle: This page describes the conventions we follow when developing Genome After-Party pipelines
---

## Input principles

Genome After-Party pipelines define inputs in a consistent manner.
The main principles are that:

- We follow the nf-core standards, of course :wink:
- Pipelines must not rely on Sanger infrastructure or environment to
  run. Pipelines must only use provided data files and metadata values,
  and query public APIs.
- No _custom_ YAML or JSON file. All parameters can be passed directly
  on the command-line. Parameters may be passed in YAML/JSON via
  Nextflow's native `-params-file` option.
- Pipelines may accept a "samplesheet" as the `--input` parameter.
  Samplesheets are used to enumerate input files, input parameters,
  output directories.
- The output directory is controlled via the `--outdir` parameter
  and all outputs conform to the [output convention](gap_conventions).
- One pipeline run should process a single assembly, passed as the
  `--fasta` parameter. Pipelines may propose a bulk-processing mode
  by allowing multiple input assemblies and output directories to be
  passed through the samplesheet.
- Parameter names should be consistent across pipelines.

## Input definition

Historically, samplesheets have had a `sample` column, as in nf-core
pipelines, that contains an identifier for the input file.
`sample` generally doesn't need to be an actual _sample_ identifier.
In particular, `sample` values may contain `/` to structure the outputs in
sub-directories. This is typically used to follow the `${specimen}/${run}`
output directory convention.

When the specimen or run identifiers are explicitly needed, they
should be requested as such in the samplesheet, rather than being inferred
from the `sample` value, cf the [readmapping](/readmapping/usage)
pipeline.
When such identifiers are optional, or to avoid introducing a breaking
change in a pipeline, the string before the `/` may be used as the
specimen identifier, cf the [variantcalling](/variantcalling/usage)
pipeline.

## Execution control

Software that can choose where temporary files are stored should have
this controlled by a pipeline parameter called `--use_work_dir_as_temp`.
When set to _true_, software should be configured to use the task
directory instead. When set to _false_, software should use the host's
temporary space.
By extension, this `--use_work_dir_as_temp` parameter can be used to
control task's `scratch` directive.

Pipelines should define CPU, memory, and runtime requirements tailored
to each software and inputs. We want to avoid nf-core's generic labels
because they are often a poor fit and lead to excessive resource
wastage.
To signify this, pipelines may remove all `withLabel:` that come with
the nf-core template.

## Outputs

Most of the conventions are already defined in the [output convention](gap_conventions)
as they form a contract with downstream consumers.

Additionally, when a pipeline merges data files, it shall name the
merged dataset `merged_${#}` and list the identifiers of the merged
data files in a file named `SOURCE.txt`.

## Testing

All pipelines must define both a `test` and `test_full` profile as per
the [testing conventions](docs/contributing/testing).
The `test` profile must work as is, without any additional parameters
or prior environment setup, e.g.

```
nextflow run . -profile singularity,test --outdir results
```

The `test_full` must also work as is, without any additional parameters,
but may require the Sanger farm environment, e.g. for database or
input paths:

```
nextflow run . -profile singularity,test_full --outdir results_full
```

The `test` profile shall be used in the `default.nf.test` test.
