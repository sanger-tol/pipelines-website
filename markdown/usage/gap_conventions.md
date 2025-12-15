---
title: Standard output directories of Genome After-Party pipelines
subtitle: This page describes the conventions we follow to organise the outputs of all Genome After-Party pipelines
---

All Genome After-Party pipelines organise their outputs in a consistent and scalable manner.

The main principles are that:

- Files are uniquely named across the entire Genome After-Party and could be mixed
  into the same directory without clashing.
- To facilitate this, filenames include all necessary identifiers such as assembly
  specimen, sequencing run. These identifiers also go into the directory names.
- Analyses that are implemented in multiple pipelines always have the same output
  name and path.
- File names are as self-explanatory as possible.

Additionally:

- All text files that can be queried by coordinates (e.g. Fasta, BED, bedGraph, VCF, some TSV)
  are compressed with `bgzip` and indexed with `tabix` in both `.tbi` and `.csi` formats.
- All other text files are compressed with `gzip` if they typically exceed 10 MB.
- Sequence alignments are in CRAM format (version 3.0) with embedded references,
  ensuring the files can be read widely and without having to pass the assembly
  Fasta file as a parameter.

Below is the canonical structure that all Genome After-Party pipelines abide by.
Placeholders are indicated with the `${...}` syntax

| Name       | Description                                                                         |
| ---------- | ----------------------------------------------------------------------------------- |
| `assembly` | Accession number of the assembly                                                    |
| `type`     | Sequencing technology. One of `pacbio`, `hic`, `illumina`, `ont`                    |
| `run`      | Identifier of the sequencing run. Usually the accession number of the data in INSDC |
| `specimen` | Identifier of the specimen. Usually a [ToLID](https://id.tol.sanger.ac.uk/)         |
| `lineage`  | Complete name of the Busco lineage, i.e. including the `_odb*` suffix               |

## Read mapping

The following outputs all come from the [read mapping](/readmapping) pipeline.
Alignment files and coverage can also be found in the [BlobToolKit](/blobtoolkit) pipeline.

- read_qc/
  - `${type}`/
    - `${specimen}`/
      - `${run}`/
        - `${type}`.`${specimen}`.`${run}`.fastqc.(html|zip)
        - `${type}`.`${specimen}`.`${run}`.filtered*fastqc.(html|zip) \_optional*
        - `${type}`.`${specimen}`.`${run}`.multiqc.html
- read_preprocess/
  - `${type}`/
    - `${specimen}`/
      - `${run}`/
        - `${type}`.`${specimen}`.`${run}`.hifi*trimmer.tar.gz \_optional*
- read_mapping/
  - `${type}`/
    - `${specimen}`/
      - `${run}`/
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.(coverage.bedGraph.gz|cram|cram.crai|flagstat|idxstats|stats.gz)
      - `${assembly}`.`${type}`.`${specimen}`.(coverage.bedGraph.gz|cram|cram.crai|flagstat|idxstats|stats.gz)

**Q**: include the aligner name ("minimap2", "bwamem2") in the filename ? (i.e. the same way we include "deepvariant" in the variantcalling output files)
**TODO**: change the name of the coverage file to match blobtoolkit

## Variant calling and analysis

The following outputs come from the [variant calling](/readmapping) and
[variant composition](/variantcomposition) pipelines.

- variant_calling/
  - `${type}`/
    - `${specimen}/`
      - `${run}`/
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.deepvariant.(vcf|g.vcf).(gz|stats.visual*report.html) \_from variantcalling*
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.deepvariant.(vcf|g.vcf).(bcftools*stats.txt.gz|frq|het|indel.hist|plot-vcfstats.(pdf|tar.gz)|roh|sites.pi.gz|snpden) \_from variantcomposition*
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.himut.vcf.(bgz|bgz.csi|bgz.tbi) _from variantcalling, optional_

**Q**: merge runs by specimen ?

## BlobToolKit

The following outputs come from the [BlobToolKit](/blobtoolkit) pipeline.

- base_content/
  - `${assembly}`.(mononuc|dinuc|trinuc|tetranuc|freq)\_windows.tsv.gz
- blobtoolkit/
  - `${assembly}`/
    - \*.json.gz
  - plots/
    - `${assembly}`.\*.png
- busco/
  - `${lineage}`/
    - `${assembly}`.`${lineage}`.(full_table.tsv.gz|missing_busco_list.tsv.gz|(single_copy|multi_copy|fragmented)\_busco_sequences.tar.gz|short_summary.(json|tsv|txt)|hmmer_output.tar.gz)
- read_mapping/
  - `${type}`/
    - `${specimen}`/
      - `${run}`/
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.coverage.1k.bedGraph.gz

**TODO**: change the `base_content` outputs to match sequencecomposition
**TODO**: drop multiqc output
**TODO**: publish the alignments too, using the same convention as in readmapping

## Sequence composition

The following outputs come from the [sequence composition](/sequencecomposition) pipeline.

- base_content/
  - k1/
    - `${assembly}`.(mononuc.1k.tsv.gz|(A|C|G|T|N|(AT|GC)\_skew|GC).1k.bedGraph.gz)
  - k2/
    - `${assembly}`.(dinuc.1k.tsv.gz|(CpG|dinucShannon).1k.bedGraph.gz)
  - k3/
    - `${assembly}`.(trinuc.1k.tsv.gz|trinucShannon.1k.bedGraph.gz)
  - k4/
    - `${assembly}`.(tetranuc.1k.tsv.gz|tetranucShannon.1k.bedGraph.gz)

## Genome note

The following outputs come from the [genome note](/genomenote) pipeline.

- ancestral_plots/
  - `${lineage}`/
    - `${assembly}`.`${lineage}`.buscopainter.(pdf|png)
- busco/ _as in blobtoolkit_
- contact_maps/
  - `${specimen}`/
    - `${assembly}`.hic.`${specimen}`.(cool|mcool|pretext|pretext.png)
- gene/
  - `${source}`/
    - `${assembly}`.`${source}`.stats.csv
- genome_note/
  - `${assembly}`.(csv|docx|md|xml|genome*note*(consistent|inconsistent).csv)
- genomescope/
- genome_stats/
  - `${assembly}`.gfastats.txt
  - `${specimen}`/
    - `${assembly}`.`${specimen}`.(completeness.stats|only.bed.gz|(asm|seq).qv|spectra-(asm|cn).\*.png|

**TODO**: drop multiqc output
**TODO**: assuming we merge all runs by specimen.

## Downloads

The following outputs come from the download pipelines:
[INSDC download](/sequencecomposition),
[Ensembl gene download](/ensemblgenedownloadd),
and [Ensembl repeat download](/ensemblrepeatdownload).

- assembly/
  - `${assembly}`.(fa.gz|sizes|assembly\_(report|stats).txt|header.sam|SOURCE)
- repeats/
  - `${source}`/
    - `${assembly}`.`${source}`.(bed.gz|masked.fa.gz)
- gene/
  - `${source}`/
    - `${assembly}`.`${source}`.(gff3.gz|(cdna|cds|pep).fa.gz)
