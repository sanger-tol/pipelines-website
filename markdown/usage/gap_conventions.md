---
title: Standard Genome After-Party outputs
subtitle: This page describes the conventions we follow to organise the outputs of the Genome After-Party pipelines
---

All Genome After-Party pipelines organise their outputs in a consistent and scalable manner.

The main principles are that:

- Files are uniquely named across the entire Genome After-Party and could be mixed
  into the same directory without clashing.
- To facilitate this, filenames include all necessary identifiers such as assembly,
  specimen, or sequencing run.
- These identifiers, except the assembly name, are also used to name the parent output
  directories, each identifier naming a different directory level.
- Analyses that are implemented in multiple pipelines always have the same output
  name and path.
- File names are as self-explanatory as possible.

Additionally:

- All text files that can be queried by coordinates (e.g. Fasta, BED, bedGraph, VCF, some TSV)
  are compressed with `bgzip` and indexed with `tabix` in both `.tbi` and `.csi` formats.
- All other text files are compressed with `gzip` if they typically exceed 10 MB.
- Sequence alignments are in CRAM format (version 3.0) with embedded references,
  ensuring the files can be read widely and without having to pass the assembly
  Fasta file as a parameter, and are all indexed with `samtools index` in both `.tbi` and `.csi` formats.

Here is the list of identifiers currently used to named outputs:

| Name       | Description                                                                          | Example value     |
| ---------- | ------------------------------------------------------------------------------------ | ----------------- |
| `assembly` | Accession number of the assembly.                                                    | `GCA_936432065.2` |
| `type`     | Sequencing technology. One of `pacbio`, `hic`, `illumina`, `ont`.                    | `hic`             |
| `run`      | Identifier of the sequencing run. Usually the accession number of the data in INSDC. | `ERR9248445`      |
| `specimen` | Identifier of the specimen. Usually a [ToLID](https://id.tol.sanger.ac.uk/).         | `icLepMacu1`      |
| `lineage`  | Complete name of the Busco lineage, i.e. including the `_odb*` suffix.               | `insecta_odb10`   |

Below is the canonical structure that all Genome After-Party pipelines abide by.
Placeholders for identifiers are indicated with the `${...}` syntax.

## Read mapping

The following outputs all come from the [read mapping](/readmapping) pipeline.
Alignment files and coverage can also be found in the [BlobToolKit](/blobtoolkit) pipeline.

- read\_mapping/
  - `${type}`/
    - `${specimen}`/
      - `${run}`/
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.`${aligner}`.(coverage.bedGraph.gz|cram|cram.crai|flagstat|idxstats|stats.gz)
        - qc/
          - `${type}`.`${specimen}`.`${run}`.fastqc.(html|zip)
          - `${type}`.`${specimen}`.`${run}`.filtered\_fastqc.(html|zip) – _optional_
          - `${type}`.`${specimen}`.`${run}`.hifi\_trimmer.tar.gz – _optional_
          - `${type}`.`${specimen}`.`${run}`.multiqc.html
      - merged/
        - _all like above but with `merged` instead of `${run}` in the file names_

**TODO**: update the overall rules to argue why we're not making a sub-directory for the aligner

**TODO**: change the name of the coverage file to match blobtoolkit

## Variant calling and analysis

The following outputs come from the [variant calling](/readmapping) and
[variant composition](/variantcomposition) pipelines.

- variant\_calling/
  - `${type}`/
    - `${specimen}/`
      - `${run}`/
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.deepvariant.(vcf|g.vcf).(gz|stats.visual\_report.html) – _from variantcalling_
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.deepvariant.(vcf|g.vcf).(stats.bcftools.txt.gz|frq|het|indel.hist|plot-vcfstats.(pdf|tar.gz)|roh|sites.pi.gz|snpden) – _from variantcomposition_
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.himut.vcf.(bgz|bgz.csi|bgz.tbi) – _from variantcalling, optional_

**Q**: merge runs by specimen ?

## BlobToolKit

The following outputs come from the [BlobToolKit](/blobtoolkit) pipeline.

- base\_content/
  - `${assembly}`.(mononuc|dinuc|trinuc|tetranuc|freq)\_windows.tsv.gz
- blobtoolkit/
  - `${assembly}`/
    - \*.json.gz
  - plots/
    - `${assembly}`.\*.png
- busco/
  - `${lineage}`/
    - `${assembly}`.`${lineage}`.(full\_table.tsv|missing\_busco\_list.tsv|(single\_copy|multi\_copy|fragmented)\_busco\_sequences.tar.gz|short\_summary.(json|tsv|txt)|hmmer\_output.tar.gz)
- read\_mapping/
  - `${type}`/
    - `${specimen}`/
      - `${run}`/
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.coverage.1k.bedGraph.gz

**TODO**: change the `base_content` outputs to match sequencecomposition

**TODO**: drop multiqc output

**TODO**: publish the alignments too, using the same convention as in readmapping

## Sequence composition

The following outputs come from the [sequence composition](/sequencecomposition) pipeline.

- base\_content/
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

- ancestral\_plots/
  - `${lineage}`/
    - `${assembly}`.`${lineage}`.buscopainter.(pdf|png)
- busco/ – _as in blobtoolkit_
- contact\_maps/
  - `${specimen}`/
    - `${assembly}`.hic.`${specimen}`.(cool|mcool|pretext|pretext.png)
- gene/
  - `${source}`/
    - `${assembly}`.`${source}`.stats.csv
- genome\_note/
  - `${assembly}`.(csv|docx|md|xml|genome\_note\_(consistent|inconsistent).csv)
- genomescope/
- genome\_stats/
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
