#!/software/treeoflife/conda/users/envs/tol/nf-core_3.2/bin/python3

"""Code to deal with pipeline RO (Research Object) Crates the sanger-tol way"""

import json
import logging
import os
import sys
from pathlib import Path

import rich_click as click
from rich.progress import BarColumn, Progress
import rocrate.rocrate
from rocrate.model.person import Person

from nf_core.pipelines.rocrate import ROCrate, CustomNextflowCrateBuilder

log = logging.getLogger(__name__)

##### Shared functions to read and transform the manifest #####

# Read and parse the manifest
def get_contributors(pipeline_obj):
    if "manifest.contributors" not in pipeline_obj.nf_config:
        log.error("No contributors field in manifest of nextflow.config")
        return

    # Grab the contributor list and convert to JSON 
    contributors_str = pipeline_obj.nf_config["manifest.contributors"]
    log.debug("manifest.contributors", contributors_str)
    # JSON uses double quotes, not single quotes
    contributors_str = contributors_str.replace("'", '"')
    for key in ["name", "affiliation", "github", "contribution", "orcid", "email"]:
        # All dictionary keys need to be quoted
        contributors_str = contributors_str.replace(f"{key}:", f"\"{key}\":")
    # Use curly brackes for dictionaries
    contributors_str = contributors_str.replace("], [", "}, {").replace("[[", "[{").replace("]]", "}]")
    contributors = json.loads(contributors_str)

    # Using a progress bar because parsing the git log could be slow
    progress_bar = Progress(
        "[bold blue]{task.description}",
        BarColumn(bar_width=None),
        "[magenta]{task.completed} of {task.total}[reset] » [bold yellow]{task.fields[name]}",
        transient=True,
        disable=os.environ.get("HIDE_PROGRESS", None) is not None,
    )
    with progress_bar:
        bump_progress = progress_bar.add_task(
            "Searching for author emails", total=len(contributors), name=""
        )

        for author in contributors:

            if "name" not in author:
                log.error(f"No name  field for author: {author}")
                sys.exit(1)

            progress_bar.update(bump_progress, advance=1, name=author["name"])

            # Fill in the email from the git history (if missing)
            if "email" not in author or not author["email"].strip():
                # get email from git log
                name = author["name"].split()[0].replace(",", "")
                email = pipeline_obj.repo.git.log(f"--author={name}", "--pretty=format:%ae", "-1")
                if email:
                    author["email"] = email
                elif "email" in author:
                    del author["email"]

            # Fix the ORCID URL
            if "orcid" in author:
                orcid = author["orcid"].strip()
                if orcid and not orcid.startswith("http"):
                    author["orcid"] = "https://orcid.org/" + orcid
                elif not orcid:
                    del author["orcid"]

            # Fix the GitHub URL
            if "github" in author:
                if author["github"].startswith("@"):
                    author["github"] = "https://github.com/" + author["github"][1:]
                elif not author["github"].startswith("http"):
                    author["github"] = "https://github.com/" + author["github"]

    return contributors

# Only update the dictionary if there's a value
def set_if_set(d, k, v):
    if v is not None:
        sv = v.strip()
        if sv:
            d[k] = sv

##### End of shared functions #####

# Future-proof the script
# nf-core 3.3 defines the expected CI as nf-test.yml but not all our
# pipelines will immediately use nf-test, so revert to ci.yml if needed
orig_build_method = CustomNextflowCrateBuilder.build
def new_build_method(self, workflow, *args, **kwargs):
    ci_workflow = kwargs["ci_workflow"]
    if not (workflow.parent / ".github" / "workflows" / ci_workflow).exists():
        kwargs["ci_workflow"] = "ci.yml"
    return orig_build_method(self, workflow, *args, **kwargs)
CustomNextflowCrateBuilder.build = new_build_method


class SangerToLROCrate(ROCrate):
    """
    Class to generate an RO Crate for a pipeline
    using author information from the Nextflow manifest
    """

    def add_main_authors(self, wf_file: rocrate.model.entity.Entity) -> None:
        """
        Add workflow contributors to the crate
        Overrides the implementation from the parent class
        """
        if "manifest.contributors" not in self.pipeline_obj.nf_config:
            log.error("No contributors field in manifest of nextflow.config")
            return

        contributors = get_contributors(self.pipeline_obj)
        log.debug("Parsed contributors", contributors)
        if not contributors:
            log.error("Empty list of contributors in manifest of nextflow.config")                
            return
        log.info(f"Found {len(contributors)} contributors")

        for author in contributors:

            # Mandatory fields
            for field in ["contribution", "orcid"]:
                if field not in author:
                    log.error(f"No {field} field for author: {author}")
                    sys.exit(1)
            log.debug(f"Adding author: {author}")

            properties = {"name": author["name"]}
            set_if_set(properties, "affiliation", author.get("affiliation"))
            set_if_set(properties, "url", author.get("github"))
            set_if_set(properties, "email", author.get("email"))

            author_entitity = self.crate.add( Person(self.crate, author["orcid"], properties=properties) )
            for mode in author["contribution"]:
                wf_file.append_to(mode, author_entitity)


@click.command()
@click.argument(
    "pipeline_dir",
    type=click.Path(exists=True),
    default=Path.cwd(),
    required=True,
    metavar="<pipeline directory>",
)
def rocrate(pipeline_dir):
    pipeline_dir = Path(pipeline_dir)
    try:
        rocrate_obj = SangerToLROCrate(pipeline_dir)
        rocrate_obj.create_rocrate(json_path=pipeline_dir)
    except (UserWarning, LookupError, FileNotFoundError) as e:    
        log.error(e)
        sys.exit(1)

if __name__ == "__main__":
    rocrate()
