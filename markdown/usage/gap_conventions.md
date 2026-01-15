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
- These identifiers, are used to name the output
  directories, each identifier naming a different directory level.
- Analyses that are implemented in multiple pipelines always have the same output
  name and path.
- File names are as self-explanatory as possible.
- File and directory naming support topping-up, e.g. adding a new specimen, a new run, etc.
  The exception is obviously merged files that will have to be overwritten.

Additionally:

- All text files that can be queried by coordinates (e.g. Fasta, BED, bedGraph, VCF, some TSV)
  are compressed with `bgzip` and indexed with `tabix` in both `.tbi` and `.csi` formats.
- All other text files are compressed with `gzip` if they typically exceed 10 MB.
- Sequence alignments are in CRAM format (version 3.0) with embedded references,
  ensuring the files can be read widely and without having to pass the assembly
  Fasta file as a parameter, and are all indexed with `samtools index` in `.crai` format.

Here is the list of identifiers currently used to named outputs:

| Name       | Description                                                                          | Example value     |
| ---------- | ------------------------------------------------------------------------------------ | ----------------- |
| `assembly` | Accession number of the assembly.                                                    | `GCA_936432065.2` |
| `type`     | Sequencing technology. One of `pacbio`, `hic`, `illumina`, `ont`, `rna`.             | `hic`             |
| `run`      | Identifier of the sequencing run. Usually the accession number of the data in INSDC. | `ERR9248445`      |
| `specimen` | Identifier of the specimen. Usually a [ToLID](https://id.tol.sanger.ac.uk/).         | `icLepMacu1`      |
| `lineage`  | Complete name of the Busco lineage, i.e. including the `_odb*` suffix.               | `insecta_odb12`   |

Additionally, tool and software names may be added to the outputs for clarity,
especially when different tools could be used, e.g. the aligner or variant-caller.

Below is the canonical structure that all Genome After-Party pipelines abide by.
Placeholders for identifiers are indicated with the `${...}` syntax.

## Read mapping

The following outputs all come from the [read mapping](/readmapping) pipeline.
Alignment files and coverage can also be found in the [BlobToolKit](/blobtoolkit) pipeline.

- read\_mapping/
  - `${type}`/
    - `${specimen}`/
      - `${run}`/
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.`${aligner}`.(coverage.bedGraph.gz|cram|cram.crai)
        - qc/
          - `${type}`.`${specimen}`.`${run}`.fastqc.(html|zip)
          - `${type}`.`${specimen}`.`${run}`.filtered\_fastqc.(html|zip) – _optional_
          - `${type}`.`${specimen}`.`${run}`.hifi\_trimmer.tar.gz – _optional_
          - `${type}`.`${specimen}`.`${run}`.multiqc.html
        - stats/
          - `${assembly}`.`${type}`.`${specimen}`.`${run}`.`${aligner}`.(flagstat|idxstats|stats.gz)
      - merged/
        - _all like above but with `merged` instead of `${run}` in the file names_

*Example*:

```text
read_mapping/hic/icLepMacu1/ERR9248445/GCA_936432065.2.hic.icLepMacu1.ERR9248445.minimap2.cram
read_mapping/hic/icLepMacu1/ERR9248445/qc/GCA_936432065.2.hic.icLepMacu1.ERR9248445.fastqc.html
read_mapping/hic/icLepMacu1/merged/GCA_936432065.2.hic.icLepMacu1.merged.minimap2.cram
```

**TODO**: change the name of the coverage file to match blobtoolkit (coverage.bedGraph -> coverage.1k.bedGraph)

## Variant calling and analysis

The following outputs come from the [variant calling](/readmapping) and
[variant composition](/variantcomposition) pipelines.

- read\_mapping/ – _as in readmapping, optional_
- variant\_analysis/
  - `${type}`/
    - `${specimen}/`
      - `${run}`/
        - `${assembly}`.`${type}`.`${specimen}`.`${run}`.`${aligner}`.`${caller}`.(vcf|g.vcf).(gz|gz.csi|gz.tbi) - _repeated for as many callers as we use_
        - composition/ – _from variantcomposition_
          - `${assembly}`.`${type}`.`${specimen}`.`${run}`.`${aligner}`.`${caller}`.(vcf|g.vcf).(frq|het|indel.hist|roh|sites.pi.gz|snpden)
        - qc/ – _from variantcomposition_
          - `${assembly}`.`${type}`.`${specimen}`.`${run}`.`${aligner}`.`${caller}`.(vcf|g.vcf).(plot-vcfstats.pdf|stats.visual\_report.html)
        - stats/ – _from variantcomposition_
          - `${assembly}`.`${type}`.`${specimen}`.`${run}`.`${aligner}`.`${caller}`.(vcf|g.vcf).(stats.bcftools.txt.gz|plot-vcfstats.tar.gz)
      - merged/
        - _like above but with `merged` instead of `${run}` in the file names_

*Example*:

```text
variant_analysis/pacbio/icLepMacu1/ERR9284044/calls/GCA_936432065.2.pacbio.icLepMacu1.ERR9284044.minimap2.deepvariant.vcf.gz
variant_analysis/pacbio/icLepMacu1/ERR9284044/stats/GCA_936432065.2.pacbio.icLepMacu1.ERR9284044.minimap2.deepvariant.vcf.stats.bcftools.txt.gz
variant_analysis/pacbio/icLepMacu1/ERR9284044/composition/GCA_936432065.2.pacbio.icLepMacu1.ERR9284044.minimap2.deepvariant.vcf.sites.pi.gz
variant_analysis/pacbio/icLepMacu1/merged/calls/GCA_936432065.2.pacbio.icLepMacu1.merged.minimap2.deepvariant.vcf.gz
```

**TODO**: `stats.visual_report.html` is currently generated by variantcalling. Move it to variantcomposition ? Make it for all VCF, not just DeepVariant ? (the plots look standard)

**TODO**: in variantcalling, make the merging of the input BAM files optional. In production we're going to run the pipeline on a samplesheet of all input CRAM files and we only want 1 VCF per CRAM as output.

## BlobToolKit

The following outputs come from the [BlobToolKit](/blobtoolkit) pipeline.

- base\_content/ – _as in sequencecomposition but only the TSV files_
- blobtoolkit/
  - `${assembly}`/
    - \*.json.gz
  - plots/
    - `${assembly}`.\*.png
- busco/
  - `${lineage}`/
    - `${assembly}`.`${lineage}`.(full\_table.tsv|missing\_busco\_list.tsv|(single\_copy|multi\_copy|fragmented)\_busco\_sequences.tar.gz|short\_summary.(json|tsv|txt)|hmmer\_output.tar.gz)
- read\_mapping/ – _as in readmapping but no merging expected_

*Example*:

```text
blobtoolkit/plots/GCA_936432065.2.snail.png
busco/insecta_odb12/GCA_936432065.2.insecta_odb12.full_table.tsv
```

**TODO**: create Busco pipeline that runs Busco and the ancestral painter (cf genomenote)

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
    - `${assembly}`.hic.`${specimen}`.merged.(cool|mcool|pretext|pretext.png)
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

**TODO**: assuming we only run the pipeline on merged read mapping

**TODO**: make the pipeline expect existing BUSCO outputs

**TODO**: move the generation of the contact map to readmapping

**TODO**: move the k-mer and assembly stats to sequencecomposition

**TODO**: at that point, the pipeline will just be aggregating stats

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

**TODO**: the ensemblgenedownload pipeline should compute the GFF stats and BUSCO scores

## Concluding remarks

- agreed (confirmed again !) that "overwrite" is the default mode. The document above should make it easier to support "topup" at some point, but "topup" is not a scenario we'll be running for the time being.
- gap_data_finder.py will be doing the symlinking but it should validate the outputs first. Validation will need a config file for each pipeline. The proposal is to have this config file in the pipeline repository
- because "overwrite" is the default, the symlinking will be at the top-level most of the time. Only in special cases (and "topup" in a future) the symlinking will have to go inside the directory structure.
- pipelines can keep multiqc, and make it as good as they want
- additionally, we'll run a multiqc at the end on everything
- something somewhere will have to convert all data tracks to big files to make a trackhub on S3
- Keep a text file that describes the output in the pipeline run folder (for instance `assets.yml`), to make it more easier when working in farm env
