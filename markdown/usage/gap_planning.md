---
title: Genome After-Party planing
subtitle: This page gives an overview of the changes we're planning for the Genome After-Party pipelines
---

# BlobToolKit

## Now

- (bug) Busco output tar files don't contain the sequences
  - Will be released as v0.9.1 or v1.0.0
  - Means all Buscos done with BlobToolKit >= v0.7.0, <= v0.9.0 will have to be rerun
    - All BlobToolKit runs with version < v0.7.0 have to be rerun anyway to enable the chromosome grids
- (feature) parameter validation for Blastn and Busco
  - For v1.0.0

## Later

- (bug) Fix accepting existing Buscos as input (useful for large genomes when we run Busco outside of the pipeline)
  - For v1.0.1

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

- k-mer stats used in genome-note
  - genomescope
  - smudgeplot
  - completeness and QV
- gfastats
- **Many** analyses, incl. repeat analyses, k-mer stats, etc

# Read mapping

## Now

- Release v1.4.0
  - Fix alignment commands and parameters. Use the same code as TreeVal &amp; co
  - HiFi-trimmer
  - Support for ULI
- Update memory settings
  - For v1.4.1

## Later

- Support for RNA-Seq
- Generate contact maps

# Genomenote

## Now

- Release v2.2.0 with all the changes already implemented for Sanger genome notes

## Later

- Support for combined maps ?
  - For v2.3.0
- Smudgeplots ?
  - For v2.3.0
- Support for pre-computed analyses ?
  - Busco
  - Contact map
  - BlobToolKit

## Long-term goal

- Retire. The Genome Note Platform will be providing the ability
  to fill a template in from Genome After-Party data

# Download pipelines

## Now

- Generate a mapping file between accession numbers, chromosome numbers, and sequence names
  - insdcdownload v2.1.0
- Generate a SAM header we can add to all read alignments
  - insdcdownload v2.1.0

## Later

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

- Rethink the naming / split of the pipelines, e.g.:
  - combine the two Ensembl pipelines into 1
  - do the RefSeq downloads in a ncbidownload pipeline
  - implement ENA support in insdcdownload or rename it ncbidownload
  - add GCA assembly support in nf-core/fetchngs
