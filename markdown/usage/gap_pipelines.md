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
  or query public APIs.
- Pipelines should accept a "samplesheet" as the `--input` parameter.
  Samplesheets are used to enumerate input files, input parameters,
  output directories.
- The output directory is controlled via the `--outdir` parameter
  and all outputs conform to the [output convention](gap_conventions).
  This can be achieved by defining publishDir directives in
  `conf/modules.config` in the pipeline, tuning `meta.id`,
  `task.ext.prefix`, etc.
- One pipeline run should process a single assembly, passed as the
  `--fasta` parameter. Pipelines may propose a bulk-processing mode
  by allowing multiple input assemblies and output directories to be
  passed through the samplesheet.
- All other command-line parameters must be values (strings, integers,
  etc), or data files. No input parameter shall have its own YAML or
  JSON format. Parameters may be only passed in YAML/JSON via Nextflow's
  native `-params-file` option.
- Parameter names should be consistent across pipelines. There is a list
  below of with the most common parameters. Before introducing a new
  parameter, search the existing pipelines for parameters that are close
  in meaning or purpose.

## Input definition

Historically, samplesheets have had a `sample` column, as in nf-core
pipelines, that contains an identifier for the input file.
`sample` generally doesn't need to be an actual _sample_ identifier.
In particular, `sample` values may contain `/` to structure the outputs in
sub-directories. This is typically used to follow the `${specimen}/${run}`
output directory convention.

When the specimen or run identifiers are explicitly needed, they
they should be requested as explicitly named columns (e.g. `specimen`,
`run`) in the samplesheet, rather than being inferred from the `sample`
value, cf the [readmapping](/readmapping/usage) pipeline.  
When such identifiers are optional, or to avoid introducing a breaking
change in a pipeline, the string before the `/` may be used as the
specimen identifier, cf the [variantcalling](/variantcalling/usage)
pipeline.

## Parameter names

Here are the most common parameters shared across pipelines.

- `--input`: samplesheet in CSV format
- `--fasta`: genome assembly in FASTA format, possibly compressed
- `--outdir`: output directory, created by Nextflow if missing
- `--align_reads`: (boolean) tell the pipeline to align the reads
- `--assembly_accession`: accession number of the assembly (GCA\_\*)
- `--*_db`: path to a local database directory or file, e.g.
  `--busco_db`, `--blastn_db`.
- `--use_work_dir_as_temp`: selection of the temporary directory, see
  below.
- `--merge_output`: a non-empty string tells the pipeline to merge
  the input files per specimen, and use that string as the name.

Note: we are still updating the pipelines to be fully consistent.

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

However, pipelines don't have to explicitly request all possible identifiers
(`${assembly}`, `${run}`, etc) just for the sake of naming the outputs
correctly. Pipelines should use the "baseName" of the input files
whenever possible and assume that Genome After-Party production
_inputs_ will be named in such a way that the naming convention
is fulfilled all the way through.

For instance, the [variantcalling pipeline](/variantcalling/usage)
takes aligned reads in BAM/CRAM and simply changes the extension to
`.deepvariant.vcf.gz`.
The naming convention applies because in production, it is run on
[readmapping](/readmapping/output) outputs that themselves comply.

Pipelines should have a MultiQC report for the whole run.
Only the HTML file needs to be published, directly in the results
directory, and it should be named `multiqc_report.html`.

Lastly, when a pipeline merges data files, it shall name the
merged dataset `merged_${#}` and list the identifiers of the merged
data files in a file named `SOURCE.txt`.
This file lists the source runs (`${accession}`), one per line.

## Testing

All pipelines must define both a `test` and `test_full` profile as per
the [testing conventions](../contributing/testing).
The `test` profile shall be used in the `default.nf.test` test.
It must also work as is, without any additional parameters
or prior environment setup from anywhere, e.g.

```
nextflow run . -profile singularity,test --outdir results
```

The `test` profile should run under 15 minutes, ideally less than
5 minutes.

The `test_full` must also work as is, without any additional parameters,
but may require the Sanger farm environment, e.g. for database or
input paths:

```
nextflow run . -profile singularity,test_full --outdir results_full
```

The `test_full` pipeline may take up to a day to complete. It is
meant to represent a real, small, species.
`test_full` is automatically run on `dev`.

Finally, all pipelines should have a Release Sentinel dataset defined
– more information on [Confluence](https://ssg-confluence.internal.sanger.ac.uk/spaces/TOL/pages/336036572/Release+Sentinel+datasets).
Release Sentinel datasets are much more expensive than `test_full`.
With often a dozen species defined, including some large ones, a
Release Sentinel dataset may take up to a week to complete.
It is therefore run less frequently, usually only before releases
when there is a certain risk of performance or quality regression.
