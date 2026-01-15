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

- (bug) Fix accepting existing Buscos as input
  - For v1.0.1

## Long-term goal

- Accept pre-computed analyses and make all the embedded analyses optional
  - Busco
  - fasta_windows
  - Read coverage
