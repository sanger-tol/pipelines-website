---
title: Genome After-Party planing
subtitle: This page gives an overview of the changes we're planning for the Genome After-Party pipelines
---

# BlobToolKit

## Now

- Release features already implemented on the development branch
  - v0.10.0 or v1.0.0 if changes below are included
- (bug) Busco output tar files don't contain the sequences
  - Means all Buscos done with BlobToolKit >= v0.7.0, <= v0.9.0 will have to be rerun
    - All BlobToolKit runs with version < v0.7.0 have to be rerun anyway to enable the chromosome grids
  - Need to be included in the next production release, by v1.0.0
- (feature) parameter validation for Blastn and Busco
  - Requested by many external users of the pipeline
  - Only thing needed to call it v1.0.0

## Later

- (bug) Fix accepting existing Buscos as input (useful for large genomes when we run Busco outside of the pipeline)
  - Ideally v1.0.0 but could be deferred to v1.0.1

## Long-term goal

- Accept pre-computed analyses and make all the embedded analyses optional
  - fasta_windows
  - Read coverage

# Busco

## Later

- Complete and release the pipeline v1.0.0
  - Regular Busco commands
  - Supports odb10 and odb12
  - Can find lineage automatically using the taxonomy
  - Make keeping the individual Fasta files an option
- Ancestral painting
  - For v1.1.0

## Long-term goal

- Reinstate the Nextflow port of Busco (to run Busco on large genomes)

# Sequence composition

## Later

- _Many_ analyses, incl. repeat analyses, k-mer stats, etc

## Long-term goal

- Take these from genomenote
  - genomescope
  - smudgeplot
  - completeness and QV
  - gfastats

# Read mapping

## Now

- Release features already implemented on the development branch
  - As v1.4.0
  - Fix alignment commands and parameters. Use the same code as TreeVal &amp; co
  - Switch to HiFi-trimmer and provide the ability to _not_ filter (that's what I actually want in production)
  - Support for ULI
- (bug) Update CPU/memory settings
  - v1.4.0 is such a big change it's unlikely to be requesting the right resources at the first attempt
  - Necessary for production but lengthy to do, so not fitting v1.4.0
  - For v1.4.1

## Later

- Support for PiMMS ?
  - For v1.5.0
- Support for RNA-Seq
  - For v1.6.0
- Generate contact maps
  - For v1.7.0

# Variant calling and composition

## Now

- (feature) add filtering options
  - For variantcomposition v0.2.0
- (feature) rearrange the outputs to be more practical
  - For variantcomposition v0.2.0
- (feature) remove features (except the stats) now in variantcomposition
  - For variantcalling v1.2.0
- (feature) make the merging of the input read alignment optional
  - For variantcalling v1.2.0

# Genomenote

## Now

- Release features already implemented on the development branch
  - As v2.2.0

## Later

- Support for combined maps ?
  - Need to check if we can use the curationpretext pipeline instead
  - For v2.3.0
- Smudgeplots ?
  - For v2.3.0
- Support for pre-computed analyses ? (Busco, contact maps, BlobToolKit)
  - For even later. TBD

## Long-term goal

- Retire.
  - Move all the analyses to other pipelines
  - The Genome Note Platform will be providing the ability
    to fill a template in from Genome After-Party data

# Assembly download pipeline

## Now

- (feature) Generate a mapping file between accession numbers, chromosome numbers, and sequence names
  - v2.1.0
- (bug) Generate a correct SAM header for the CRAM files
  - v2.1.0

## Later

- Take over running gfastats from the genome-note pipeline
  - v2.2.0

# Ensembl download pipelines

## Later

- Support the new FTP / data release model of Ensembl
- The insdcdownload pipeline should:
  - download RefSeq annotation ?
- The ensemblgenedownload pipeline should
  - change the sequence names in the GFF file to match the accession numbers
  - compute the GFF stats and its BUSCO scores
  - compute gene density tracks
- The ensemblrepeatdownload pipeline should
  - compute repeat density tracks
  - download RepeatModeler models

## Long-term goal

- Rethink the naming / split of all download pipelines, e.g.:
  - combine the two Ensembl pipelines into one ?
  - pipeline to download from RefSeq (refseqdownload ?)
  - implement ENA support in insdcdownload ?
  - rename insdcdownlod it ncbidownload
  - add GCA assembly support in nf-core/fetchngs ?
