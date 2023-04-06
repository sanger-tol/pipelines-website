<?php
$title = 'Tools';
$subtitle = 'Tools and software developed at the Sanger Tree of Life, to help create high quality genome assemblies.';

$md_github_url = 'https://github.com/sanger-tol/pipelines-website/blob/main/markdown/tools/README.md'; // For the footer
$md_github_raw_url = 'https://raw.githubusercontent.com/sanger-tol/pipelines-website/software/markdown/tools/README.md'; // For rendering the page

// Render from README
$md_contents = file_get_contents($md_github_raw_url);
if ($md_contents) {
    file_put_contents($md_contents);
}

$md_trim_before = '## Table of contents';
include '../../includes/header.php';
include '../../includes/footer.php';
?>
