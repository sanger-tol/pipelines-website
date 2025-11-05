---
title: Shared modules and sub-workflows
subtitle: How to get started with our open repository of Nextflow modules and sub-workflows
---

[`sanger-tol/nf-core-modules`](https://github.com/sanger-tol/nf-core-modules) is a repository hosting Nextflow DSL2 modules for the Sanger Tree of Life organization.
It follows the same principles as the [nf-core modules repository](https://github.com/nf-core/modules).

On this page, you'll find how to use the repository in your pipelines.

## Modules

The module files hosted in this repository define a set of processes for software tools that allow you to share and add common functionality across multiple pipelines in a modular fashion.

We use a helper command in the `nf-core/tools` package that uses the GitHub API to obtain the relevant information for the module files present in the [`modules/`](modules/) directory of this repository. This includes using `git` commit hashes to track changes for reproducibility purposes, and to download and install all of the relevant module files.

1. Install the latest version of [`nf-core/tools`](https://github.com/nf-core/tools#installation) version 3.4 or later.
   Version 3.3 and earlier do **not** support sub-workflows recorded in this repository.
2. List the available modules:

   ```bash
   nf-core modules --git-remote https://github.com/sanger-tol/nf-core-modules.git list remote
   ```

   ```console
                                             ,--./,-.
            ___     __   __   __   ___      /,-._.--~\
      |\ | |__  __ /  ` /  \ |__) |__          }  {
      | \| |       \__, \__/ |  \ |___      \`-._,-`-,
                                             `._,._,'

      nf-core/tools version 3.4.1 - https://nf-co.re

   INFO     Modules available from https://github.com/sanger-tol/nf-core-modules.git (main):

   ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
   ┃ Module Name                ┃
   ┡━━━━━━━━━━━━━━━━━━━━━━━━━━━━┩
   │ ancestral/extract          │
   │ ancestral/plot             │
   │ asmstats                   │
   │ bedtools/bamtobedsort      │
   │ blobtoolkit/generatecsv    │
   ..truncated..
   ```

3. Install the module in your pipeline directory:

   ```bash
   nf-core modules --git-remote https://github.com/sanger-tol/nf-core-modules.git install asmstats
   ```

   ```console
                                             ,--./,-.
            ___     __   __   __   ___      /,-._.--~\
      |\ | |__  __ /  ` /  \ |__) |__          }  {
      | \| |       \__, \__/ |  \ |___      \`-._,-`-,
                                             `._,._,'

      nf-core/tools version 3.4.1 - https://nf-co.re

   INFO     Installing 'asmstats'
   INFO     Use the following statement to include this module:

    include { ASMSTATS } from '../modules/sanger-tol/asmstats/main'
   ```

4. Use the `include` statement as is in your workflow file `workflows/name.nf`.
   If you want to add the module do a sub-workflow such as `subworkflows/local/name/main.nf`,
   you will need to adjust the path accordingly:

   ```nextflow
   include { ASMSTATS } from '../../../modules/sanger-tol/asmstats/main'
   ```

5. Check that a locally installed sanger-tol module is up-to-date compared to the one hosted in this repo:

   ```bash
   nf-core modules --git-remote https://github.com/sanger-tol/nf-core-modules.git lint asmstats
   ```

   ```console
                                             ,--./,-.
            ___     __   __   __   ___      /,-._.--~\
      |\ | |__  __ /  ` /  \ |__) |__          }  {
      | \| |       \__, \__/ |  \ |___      \`-._,-`-,
                                             `._,._,'

      nf-core/tools version 3.4.1 - https://nf-co.re

      INFO     Linting pipeline: '.'
      INFO     Linting module: 'asmstats'

   ╭─ [!] 2 Module Test Warnings ─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────╮
   │              ╷                                     ╷                                                                                                                                         │
   │ Module name  │ File path                           │ Test message                                                                                                                            │
   │╶─────────────┼─────────────────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────╴│
   │ asmstats     │ modules/sanger-tol/asmstats/main.nf │ Unable to connect to container registry, code:  404, url: https://community.wave.seqera.io/library/seqtk_perl:37201934bb74266e          │
   │ asmstats     │ modules/sanger-tol/asmstats/main.nf │ Container versions do not match                                                                                                         │
   │              ╵                                     ╵                                                                                                                                         │
   ╰──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────╯
   ╭───────────────────────╮
   │ LINT RESULTS SUMMARY  │
   ├───────────────────────┤
   │ [✔]  34 Tests Passed  │
   │ [!]   2 Test Warnings │
   │ [✗]   0 Tests Failed  │
   ╰───────────────────────╯
   ```

6. Remove the module from the pipeline repository if required:

   ```bash
   nf-core modules --git-remote https://github.com/sanger-tol/nf-core-modules.git remove asmstats
   ```

   ```console
                                             ,--./,-.
            ___     __   __   __   ___      /,-._.--~\
      |\ | |__  __ /  ` /  \ |__) |__          }  {
      | \| |       \__, \__/ |  \ |___      \`-._,-`-,
                                             `._,._,'

      nf-core/tools version 3.4.1 - https://nf-co.re

   INFO     Removed files for 'asmstats' and its dependencies 'asmstats'.
   ```

## Sub-workflows

The sub-workflow files hosted in this repository define arrangements of existing software tools (modules) that are frequently seen across pipelines.
Like modules, sub-workflows are managed with the `nf-core/tools` package and allow you to share and add common functionality across multiple pipelines in a modular fashion.

Sub-workflows are stored in the [`subworkflows/`](subworkflows/) directory of this repository.

1. List the available sub-workflows:

   ```bash
   nf-core subworkflows --git-remote https://github.com/sanger-tol/nf-core-modules.git list remote
   ```

   ```console
                                             ,--./,-.
            ___     __   __   __   ___      /,-._.--~\
      |\ | |__  __ /  ` /  \ |__) |__          }  {
      | \| |       \__, \__/ |  \ |___      \`-._,-`-,
                                             `._,._,'

      nf-core/tools version 3.4.1 - https://nf-co.re

   INFO     Subworkflows available from https://github.com/sanger-tol/nf-core-modules.git (main):

   ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
   ┃ Subworkflow Name               ┃
   ┡━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┩
   │ ancestral_annotation           │
   │ bam_samtools_merge_markdup     │
   │ cram_map_illumina_hic          │
   ..truncated..
   ```

2. Install the sub-workflow in your pipeline directory:

   ```bash
   nf-core subworkflows --git-remote https://github.com/sanger-tol/nf-core-modules.git install bam_samtools_merge_markdup
   ```

   ```console
                                             ,--./,-.
            ___     __   __   __   ___      /,-._.--~\
      |\ | |__  __ /  ` /  \ |__) |__          }  {
      | \| |       \__, \__/ |  \ |___      \`-._,-`-,
                                             `._,._,'

      nf-core/tools version 3.4.1 - https://nf-co.re

   INFO     Installing 'bam_samtools_merge_markdup'
   INFO     Use the following statement to include this subworkflow:

    include { BAM_SAMTOOLS_MERGE_MARKDUP } from '../subworkflows/sanger-tol/bam_samtools_merge_markdup/main'
   ```

   This will automatically install module (and sub-workflow) dependencies:

   ```console
   $ ls modules/nf-core/samtools/
   faidx  markdup  merge
   ```

3. Import the sub-workflow in your workflow (expected to live in `workflows/`):

   ```nextflow
   include { BAM_SAMTOOLS_MERGE_MARKDUP } from '../subworkflows/sanger-tol/bam_samtools_merge_markdup/main'
   ```

4. Check that a locally installed sanger-tol sub-workflow is up-to-date compared to the one hosted in this repo:

   ```bash
   nf-core subworkflows --git-remote https://github.com/sanger-tol/nf-core-modules.git lint bam_samtools_merge_markdup
   ```

   ```console
                                             ,--./,-.
            ___     __   __   __   ___      /,-._.--~\
      |\ | |__  __ /  ` /  \ |__) |__          }  {
      | \| |       \__, \__/ |  \ |___      \`-._,-`-,
                                             `._,._,'

      nf-core/tools version 3.4.1 - https://nf-co.re

      INFO     Linting pipeline: '.'
      INFO     Linting subworkflow: 'bam_samtools_merge_markdup'

   ╭───────────────────────╮
   │ LINT RESULTS SUMMARY  │
   ├───────────────────────┤
   │ [✔]  26 Tests Passed  │
   │ [!]   0 Test Warnings │
   │ [✗]   0 Tests Failed  │
   ╰───────────────────────╯
   ```

5. Remove the sub-workflow from the pipeline repository if required:

   ```bash
   nf-core subworkflows --git-remote https://github.com/sanger-tol/nf-core-modules.git remove bam_samtools_merge_markdup
   ```

   ```console
                                             ,--./,-.
            ___     __   __   __   ___      /,-._.--~\
      |\ | |__  __ /  ` /  \ |__) |__          }  {
      | \| |       \__, \__/ |  \ |___      \`-._,-`-,
                                             `._,._,'

      nf-core/tools version 3.4.1 - https://nf-co.re

   INFO     Removed files for 'asmstats' and its dependencies 'asmstats'.
   ```

## Cross-organisation sub-workflows

"Cross-organisation" sub-workflows are sub-workflows that contain components from both `nf-core/modules` and `sanger-tol/nf-core-modules`.
They require the version 3.4 (or later) of the `nf-core/tools` package.

### Writing cross-organisation sub-workflows

A reference example exists in the nf-core test repository <https://github.com/nf-core-test/modules>.

1. Write sub-workflows `.nf` files that refer to locations in both `sanger-tol` and `nf-core`.
   [Example](https://github.com/nf-core-test/modules/blob/main/subworkflows/nf-core-test/get_genome_annotation/main.nf#L1-L2)

2. In `meta.yml`:
   1. Change the first line to

      ```yaml
      # yaml-language-server: $schema=https://raw.githubusercontent.com/nf-core-test/modules/main/subworkflows/yaml-schema.json
      ```

      [Example](https://github.com/nf-core-test/modules/blob/main/subworkflows/nf-core-test/get_genome_annotation/meta.yml#L1).
      This ensures that the right schema will be used to validate the file.
      This schema differs from the default one by allowing keys such as `git_remote` under "components", which are used to
      indicate modules that live in the `nf-core/modules` repository, see next point.

   2. Add a `git_remote` key that maps to the nf-core modules repository.

3. In `modules/`, do _not_ add nf-core modules.
   When installing a sub-workflow, the `nf-core` tools command will identify the nf-core modules from the `git_remote` key
   explained above, and install those modules automatically.

### Testing cross-organisation sub-workflows

Tests for a cross-organisation sub-workflow also need a copy of the nf-core modules to run.
We use functions from the `nft-utils` plugin (version 0.0.7 or later), which is declared as a test dependency in `nf-test.config`.
The functions will automatically download (and clean up) nf-core modules when running tests.
These functions must be called from the sub-workflow's tests (e.g. the `main.nf.test` file).
Take the [hic_mapping](https://github.com/sanger-tol/nf-core-modules/blob/main/subworkflows/sanger-tol/hic_mapping/tests/main.nf.test)
sub-workflow as an example.

In the _setup_ phase:

1. Call `nfcoreInitialise` to initialise a new "library" directory.
2. Call `nfcoreInstall` to install all the nf-core modules you need in that library.
   You need to keep this list in sync with the modules declared in `meta.yml`.
3. Call `nfcoreLink` to link the nf-core modules from the above "library" into the test's "modules" directory.

And in the _cleanup_ phase:

1. Call `nfcoreUnlink`.

(and that's all !)

### Using cross-organisation sub-workflows in pipelines

Pipelines need a few modifications to work seamlessly with cross-organisation sub-workflows.

1. In `.pre-commit-config.yaml`, add extra lines to ignore sanger-tol modules and sub-workflows the same way nf-core ones are ignored:

   ```diff
   --- a/.pre-commit-config.yaml
   +++ b/.pre-commit-config.yaml
   @@ -15,6 +15,8 @@ repos:
                  .*ro-crate-metadata.json$|
                  modules/nf-core/.*|
                  subworkflows/nf-core/.*|
   +              modules/sanger-tol/.*|
   +              subworkflows/sanger-tol/.*|
                  .*\.snap$
            )$
         - id: end-of-file-fixer
   @@ -23,5 +25,7 @@ repos:
                  .*ro-crate-metadata.json$|
                  modules/nf-core/.*|
                  subworkflows/nf-core/.*|
   +              modules/sanger-tol/.*|
   +              subworkflows/sanger-tol/.*|
                  .*\.snap$
            )$
   ```

2. `nf-test.config` needs similar rules:

   ```diff
   --- a/nf-test.config
   +++ b/nf-test.config
   @@ -9,7 +9,7 @@ config {
      configFile "tests/nextflow.config"

      // ignore tests coming from the nf-core/modules repo
   -    ignore 'modules/nf-core/**/*', 'subworkflows/nf-core/**/*'
   +    ignore 'modules/nf-core/**/*', 'subworkflows/nf-core/**/*', 'modules/sanger-tol/**/*', 'subworkflows/sanger-tol/**/*'

      // run all test with defined profile(s) from the main nextflow.config
      profile "test"
   ```

   It also meeds to refers to the version 0.0.7 or later of the `nft-utils` plugin (`load "nft-utils@` line).
