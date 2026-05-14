---
title: Pipeline logo
subtitle: Instructions for creating the image logos for a pipeline
---

`render_logo.py` generates logos for Sanger Tree of Life pipelines.
Logos use the same layout as the nf-core logos, but customised to
follow Sanger branding guidelines (Roboto font, specific shades of blue).

In Tree of Life, the script is already installed at
`/software/treeoflife/bin/render_logo.py`, and its runtime dependencies are
already available. There is nothing to install.

## Quick start

In most cases you only need to point the script at the pipeline directory that
contains `.nf-core.yml`:

```bash
/software/treeoflife/bin/render_logo.py --pipeline-dir /path/to/pipeline
```

This command:

- reads `template.org` and `template.name` from `/path/to/pipeline/.nf-core.yml`
- writes output files under `/path/to/pipeline/docs/images/`
- generates both dark and light variants
- prints the paths of the generated files on success

By default the output filenames are:

- `/path/to/pipeline/docs/images/<org>-<name>_logo_dark.svg`
- `/path/to/pipeline/docs/images/<org>-<name>_logo_dark.png`
- `/path/to/pipeline/docs/images/<org>-<name>_logo_light.svg`
- `/path/to/pipeline/docs/images/<org>-<name>_logo_light.png`

If you are already in the pipeline directory, this is equivalent:

```bash
/software/treeoflife/bin/render_logo.py --pipeline-dir .
# or
/software/treeoflife/bin/render_logo.py
```

If `.nf-core.yml` is missing or you want to override its values, supply the name
as the positional argument, e.g. `--org`:

```bash
/software/treeoflife/bin/render_logo.py my-pipeline \
  --org sanger-tol \
  --pipeline-dir /path/to/pipeline
```

## How the script works

The script uses the bundled `sanger-tol.template.svg` file as the geometry and
style source. It updates the placeholder text, applies the appropriate text
colour for each output mode, and then exports both SVG and PNG versions.

The output size is controlled by `--height`, which defaults to `4cm`. Width is
calculated automatically to preserve the aspect ratio. If the replacement name
is wider than the placeholder in the template, the script increases the SVG
canvas width just enough to keep the text clear of the embedded image while
preserving the original right-hand padding.

To estimate how wide the new name will be, the script measures rendered text via
Inkscape and Pillow. On the standard Tree of Life installation this should be
fully available already. If rendered measurement fails for any reason, the
script falls back to a simple width estimate so that logo generation can still
continue.

Two path rules are worth knowing:

- `--config` and `--output-dir` are resolved relative to `--pipeline-dir`
- `--template` is resolved as given and is not automatically rebased onto `--pipeline-dir`

## Common variations

Generate only the light logo:

```bash
/software/treeoflife/bin/render_logo.py --pipeline-dir /path/to/pipeline --mode light
```

Write output somewhere other than `docs/images` within the pipeline directory:

```bash
/software/treeoflife/bin/render_logo.py \
  --pipeline-dir /path/to/pipeline \
  --output-dir assets/logo
```

Use a different metadata file under the same pipeline directory:

```bash
/software/treeoflife/bin/render_logo.py \
  --pipeline-dir /path/to/pipeline \
  --config config/custom.nf-core.yml
```

Use an explicit template file:

```bash
/software/treeoflife/bin/render_logo.py \
  --pipeline-dir /path/to/pipeline \
  --template /path/to/custom-template.svg
```

## Complete usage

```text
usage: render_logo.py [-h] [--org ORG] [--pipeline-dir PIPELINE_DIR]
                      [--config CONFIG] [--template TEMPLATE]
                      [--output-dir OUTPUT_DIR] [--mode {dark,light,all}]
                      [--height HEIGHT] [--inkscape INKSCAPE]
                      [name]
```

### Positional argument

- `name`: Replacement text for the project label. If omitted, the script uses
  `template.name` from the selected `.nf-core.yml` file.

### Options

- `-h`, `--help`: Show the built-in command help and exit.
- `--org ORG`: Organisation used in the output filename prefix. If omitted, the
  script uses `template.org` from the selected `.nf-core.yml` file.
- `--pipeline-dir PIPELINE_DIR`: Pipeline directory used as the base for
  resolving `--config` and `--output-dir`. Defaults to the current directory.
- `--config CONFIG`: Path to the metadata file used to discover `template.org`
  and `template.name`. Defaults to `.nf-core.yml`, resolved relative to
  `--pipeline-dir`.
- `--template TEMPLATE`: Path to the SVG template file. Defaults to the bundled
  `sanger-tol.template.svg` next to the script.
- `--output-dir OUTPUT_DIR`: Directory for generated SVG and PNG files. Defaults
  to `docs/images`, resolved relative to `--pipeline-dir`.
- `--mode {dark,light,all}`: Select which colour variant to generate. The
  default is `all`, which writes both dark and light outputs.
- `--height HEIGHT`: Output height written into the SVG, for example `4cm` or
  `120px`. Width is calculated automatically to preserve aspect ratio.
- `--inkscape INKSCAPE`: Command or full path for the Inkscape executable used
  for PNG export and text measurement. On Tree of Life systems the default
  `inkscape` should already work.

### Behaviour and validation details

- The script requires a non-empty project name and organisation. You can supply
  them through `.nf-core.yml`, through the command line, or by combining both.
- If the selected config file does not exist, you must provide both `name` and
  `--org` on the command line.
- The script refuses empty values such as `--org ""`.
- The script checks that the template file exists before rendering.
- The script checks that the requested Inkscape executable can be found on
  `PATH` unless you provide an explicit full path.
- The script creates the output directory if it does not already exist.
- On success it prints one output path per line.
