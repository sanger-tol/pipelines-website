---
title: Automating Genome Note publications
subtitle: Bethan Yates, Tyler Chafin
type: tutorial
start_date: '2024-10-11'
start_time: '13:00+00:00'
end_date: '2024-10-11'
end_time: '15:00+00:00'
location_name: Biodiversity Genomics Academy 2024
location: https://thebgacademy.org/
youtube_embed: https://www.youtube.com/watch?v=MDEnuE3iYp0
---

> Organised by [Biodiversity Genomics Academy](https://thebgacademy.org/)

# Introduction

Biodiversity Genomics Academy 2024 (BGA24) is a series of free, open to all,
nline-only, short and interactive sessions on how to use the bioinformatics tools and approaches that underpin the Earth BioGenome Project and the field as a whole.

More information [on our website](https://thebgacademy.org/).

# Description

In this session you will learn how to automatically generate content for genome note publications using our suite of [ToL analysis pipelines](https://pipelines.tol.sanger.ac.uk).

## Part 1: An introduction to Genome Notes

By the end of this part you will have:

1. Obtained an overview of the Tree of Life programme and our genome note concept
2. Understood the purpose of a genome note and the information it reports
3. Gained an idea of why automating genome note production is important and how this can be achieved

## Part 2: Hands on - Running the sanger-tol/genomenote pipeline and exploring the outputs

By the end of this part you will be able to:

1. Run the sanger-tol/genomenote pipeline to produce a genome note document
2. Understand how to use the pipeline to generate genome notes for your own genomes
3. Gained an idea of how different pipelines can be combined to go from raw sequencing data to a publication reporting a genome assembly

## The nextflow command to run the pipeline

    nextflow run genomenote/main.nf \
    -profile docker,arm \
    -params-file assets/BGA-test.json \
    --outdir BGA_test_results

## Useful links

- The [sanger-tol/genomenote pipeline](https://pipelines.tol.sanger.ac.uk/).
- The published [genome note](https://wellcomeopenresearch.org/articles/9-539) for the species used in this session.
- The HiGlass link to the [Hi-C map](https://genome-note-higlass.tol.sanger.ac.uk/l/?d=N0lSy7fGQ7SSE1afN54MCg) for the species used in this session.
- The [Blobtool viewer](https://blobtoolkit.genomehubs.org/view/GCA_963859965.1/dataset/GCA_963859965.1/blob#Filters) for the species used in this session.
- The [Genome After Party](https://gap.cog.sanger.ac.uk/Ceramica_pisi/) for the species used in this session.

# Prerequisites

1. Familiarity with linux command line basics (cd, mv, rm)
2. Knowledge of the Nano editor will be helpful
