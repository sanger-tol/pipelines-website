# sanger-tol/pipelines: Usage

Welcome! You've found this usage guide for the **Sanger-ToL pipelines**. Well done! :tada:

Yes, Nextflow is a complex workflow system. Being here means you've considered analysing your data in a pipeline, you've chosen Nextflow (great choice!), and you've found us (woohoo!). That's already a big set of accomplishments! :star2:

Nextflow has [official documentation](https://www.nextflow.io/docs/latest/). This page, however, is meant as a complement for beginners in bioinformatics and Nextflow. We'll walk you through the **minimum setup** you need for running Nextflow, and then show you how to launch the Sanger-ToL pipelines step by step. If you're already familiar with some parts, feel free to skip ahead :racehorse:.

Before getting started, let's set a baseline: this documentation assumes your computer runs **Linux** or **macOS**, since we'll be working a lot with the command line interface (CLI). If you're on Windows, don't worry - you can still join in by setting up a Linux-like environment. The easiest way is via **PowerShell + WSL**. See [this guide](https://seqera.io/blog/setup-nextflow-on-windows/) for instructions.

If you spot errors or have any feedback, please let us know through GitHub issues! Real people behind the pipelines (yes, us :smile:) will read it.

Ready? Let's get started! :rocket:

## Introduction

I will take you through:

1. Setting up the environment, like a stage, for Nextflow to perform on :mirror_ball:
2. Installing Nextflow, our performer :man_dancing:, with dependencies as a set of instrument Nextflow perform with :violin:
3. Running Sanger-ToL pipelines, the symphonies our performer play :musical_score:

If that's the concert you came for, you're in the right place. :wink:

## Setting up the Environment

### Homebrew :beer:

First stop: `Homebrew`. It’s a package manager that makes installing software simple and consistent across macOS and Linux.

For Sanger users, `Homebrew` is already available in the software database. Just search for it and hit the install button.

If you install it yourself, please paste the following command into a macOS Terminal or Linux shell:

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Homebrew will explain what it plans to do and ask for permission before proceeding.

If you’re curious (or just love documentation), check out the [Homebrew website](https://brew.sh/) for more details.

Now you’ve got `Homebrew`. Cheers! :beers: Let's move on to the next step.

### Dependencies :toolbox:

Nextflow is writen in `Groovy`, which runs on the **Java Virtual Machine (JVM)**. That means you’ll need a Java Development Kit (JDK). The easiest option is to install `OpenJDK`, the open-source JDK that includes everything you need for `Groovy` and Nextflow.

```bash
brew install openjdk
```

That’s it - you’ve got Java installed! :heavy_check_mark:

You might also have heard about `Conda` from other docs. `Conda` is both a package manager and an environment manager. Since we’re already using `Homebrew`, you don’t strictly need `Conda` here. But if you’d like to have it anyway (it’s handy in many bioinformatics workflows), we recommend the lighter-weight, community-driven version `Miniforge`. It’s small, simple, and actively maintained by the conda-forge community :heartbeat:.

```bash
brew install --cask miniforge
```

This gives you Conda without the bloat. :fairy:

### Containers :passenger_ship:

One of the best parts of running this pipeline is that you don’t need to install all the individual tools yourself - they’re all neatly wrapped in containers. Think of a container as a 'ready-made costume :dress:' for our performer: it contains all the software the pipeline needs, so your system stays clean and free from version conflicts. And why the 'ship' emoji? Ah, because our performer needs many costumes from all around the world - and We will ship them in! A true luxery :nail_care:.

There are a few container options listed in the `README` (the front page of this pipeline on GitHub). Here, we’ll show an example using `Docker` (the dock where all those ships full of costumes arrive):

```bash
brew install docker
```

I personally like `Docker` because its logo is an adorable whale :whale: carrying containers on its back. :smile:

And that’s it! :popcorn: You now have everything prepared for our Nextflow performer to take the stage. :dancer:

## Nextflow :rainbow:

Now let’s invite our performer onto the stage by downloading Nextflow:

```bash
curl -s https://get.nextflow.io | bash
```

Then, give Nextflow permission to perform by making it executable:

```bash
chmod +x nextflow
```

Next comes a slightly more involved step. We need to place Nextflow somewhere your computer recognises as an executable path - basically telling it: “Hey, we have a performer coming in. Let them on stage.”

Let’s create a little dressing room and move Nextflow there:

```bash
mkdir -p $HOME/.local/bin/
mv nextflow $HOME/.local/bin/
```

Now, we need to let the computer know where the dressing room is. To do this, edit your hidden configuration file (`.bashrc` or `.zshrc`) in your Home directory: in MacOS, open **Finder** app, go to the **Go** menu at the top of the screen, and select **Home**; then press **Shift**, **Command**, **.** on your keyboard at the same time to show hidden files; find the `.bashrc` or `.zshrc` file and open it in TextEdit, add this line at the end:

`export PATH="$PATH:$HOME/.local/bin"`

Save, and close the file.

Finally, let's check if the performer is ready. In your Terminal (or Linux shell), run:

```bash
nextflow info
```

If you see version details appear, congratulations! Your Nextflow performer is ready to shine on stage. :sparkles:

# Pipeline :musical_note:

Now let's get the pipeline running and make some music with your data!
The following sections provide the official usage guidelines. Don't worry if some parts seem unclear or if you encounter issues – we're here to help! Feel free to [send me your questions](https://forms.gle/Rn1bWMXamhdoBBeDA) or open an issue on our repository.

