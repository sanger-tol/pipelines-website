<?php
$title = 'Tools';
$subtitle = 'Tools and software developed at the Sanger Tree of Life, to help create high quality genome assemblies.';

$markdown_fn = dirname(__FILE__) . '/../../markdown/tools/README.md'; // Local cache
$md_github_url = 'https://github.com/sanger-tol/pipelines-website/blob/main/markdown/tools/README.md'; // For the footer
$md_github_raw_url = 'https://raw.githubusercontent.com/sanger-tol/pipelines-website/software/markdown/tools/README.md'; // For rendering the page

// Fetch readme if cache is not found or more than 24 hours old
if (!file_exists($markdown_fn) || filemtime($markdown_fn) < time() - 60 * 60 * 24) {
    // Download the readme and cache
    // Build directories if needed
    if (!is_dir(dirname($markdown_fn))) {
        mkdir(dirname($markdown_fn), 0777, true);
    }
    $md_contents = file_get_contents($md_github_raw_url);
    if ($md_contents) {
        file_put_contents($markdown_fn, $md_contents);
    }
}

$md_trim_before = '## Table of contents';
include '../../includes/header.php';
include '../../includes/footer.php';
?>
