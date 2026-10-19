<?php
$title = 'subworkflows/<br class="d-sm-none">' . $escape($subworkflow['name']);
$subtitle = $markdown->text($subworkflow['description'] ?? '');
$github_url = 'https://github.com/sanger-tol/nf-core-modules/tree/main/' .
    implode('/', array_map('rawurlencode', explode('/', dirname($subworkflow['github_path']))));
$install_command = 'nf-core subworkflows --git-remote https://github.com/sanger-tol/nf-core-modules.git install ' .
    basename(dirname($subworkflow['github_path']));
$pipelines = $pipeline_links[$subworkflow['id']] ?? [];
$header_html = '<p class="mb-0">';
foreach ($subworkflow['keywords'] as $tag) {
    $header_html .= '<a href="/subworkflows?' . $escape(http_build_query(['keyword' => $tag])) .
        '" class="badge module-topic">' . $escape($tag) . '</a> ';
}
$header_html .= '</p>';
$no_auto_toc = true;
$no_print_content = true;
$mainpage_container = false;
include __DIR__ . '/../header.php';
?>
<style>
    .mainpage-heading h1 { overflow-wrap: anywhere; }
    .mainpage-heading .module-topic { white-space: normal; }
    .subworkflow-detail, .subworkflow-subheader { overflow-wrap: anywhere; }
    .subworkflow-detail code, .subworkflow-detail .module-row-name-id { white-space: normal; overflow-wrap: anywhere; }
    .subworkflow-subheader .module-install-cmd { max-width: 48rem; }
</style>
<div class="container-fluid mainpage-subheader-heading chevron-down pt-5 subworkflow-subheader">
    <div class="container d-flex flex-column align-items-center">
        <div class="input-group module-install-cmd w-100 mb-3">
            <span class="input-group-text"><i class="fas fa-terminal" aria-hidden="true"></i></span>
            <input type="text" class="form-control input code" id="module-install-cmd-text" data-autoselect
                value="<?php echo $escape($install_command); ?>" aria-label="Subworkflow install command" readonly>
            <button class="btn btn-outline-secondary copy-txt" data-bs-target="module-install-cmd-text" data-bs-toggle="tooltip" data-bs-placement="left" title="Copy to clipboard" aria-label="Copy install command" type="button"><i class="fas fa-clipboard px-1" aria-hidden="true"></i></button>
        </div>
        <p><a href="<?php echo $escape($github_url); ?>" class="subheader-link"><i class="fab fa-github" aria-hidden="true"></i> <?php echo $escape($github_url); ?></a></p>
    </div>
</div>

<div class="container-xxl main-content subworkflow-detail">
    <div class="row flex-wrap-reverse flex-lg-wrap ms-lg-5">
        <div class="col-12 col-lg-9">
            <article class="module module-page-content mb-2">
                <h2 id="description" class="ms-n3"><i class="far fa-book fa-fw" aria-hidden="true"></i> Description
                    <a href="#description" class="header-link scroll_to_link" aria-label="Link to description"><span class="fas fa-link" aria-hidden="true"></span></a>
                </h2>
                <div class="ps-2"><?php echo $markdown->text($subworkflow['description'] ?? ''); ?></div>
                <div class="module-params">
                    <?php foreach (['input' => 'Input', 'output' => 'Output'] as $direction => $label): ?>
                        <section class="module-<?php echo $direction; ?> mt-5" aria-labelledby="<?php echo $direction; ?>">
                            <h2 id="<?php echo $direction; ?>" class="text-success ms-n3"><i class="fad fa-sign-<?php echo $direction === 'input' ? 'in' : 'out'; ?> fa-fw" aria-hidden="true"></i> <?php echo $label; ?>
                                <a href="#<?php echo $direction; ?>" class="header-link scroll_to_link" aria-label="Link to <?php echo strtolower($label); ?>"><span class="fas fa-link" aria-hidden="true"></span></a>
                            </h2>
                            <?php $fields = module_parameter_fields($subworkflow[$direction]); ?>
                            <?php if (!$fields): ?>
                                <p class="text-muted">No <?php echo $direction; ?> metadata available.</p>
                            <?php else: ?>
                                <div class="row border-bottom border-3 d-none d-md-flex">
                                    <div class="col-12 col-md-3"><span class="text-muted">Name</span></div>
                                    <div class="col-12 col-md-6"><span class="text-muted">Description</span></div>
                                    <div class="col-12 col-md"><span class="text-muted float-end">Pattern</span></div>
                                </div>
                                <?php $channel = ''; ?>
                                <?php foreach ($fields as $field_index => $field): ?>
                                    <?php if ($field['channel'] !== '' && $field['channel'] !== $channel): ?>
                                        <h3 class="h5 mt-3"><?php echo $escape($field['channel']); ?></h3>
                                    <?php endif; ?>
                                    <?php
                                    $channel = $field['channel'];
                                    $field_id = $direction . '-' . $field_index . '-' .
                                        strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $field['name']));
                                    ?>
                                    <div class="row border-bottom align-items-center parameter-row">
                                        <div class="col-12 col-md-3 small-h">
                                            <h4 id="<?php echo $escape($field_id); ?>" class="module-row-name-id">
                                                <code><?php echo $escape($field['name']); ?></code>
                                                <?php if ($field['type'] !== ''): ?><span class="text-muted"> (<?php echo $escape($field['type']); ?>)</span><?php endif; ?>
                                                <a href="#<?php echo $escape($field_id); ?>" class="header-link scroll_to_link me-2" aria-label="Link to <?php echo $escape($field['name']); ?>"><span class="fas fa-link" aria-hidden="true"></span></a>
                                            </h4>
                                        </div>
                                        <div class="col-12 col-md-<?php echo $field['pattern'] !== '' ? '5' : '7'; ?> small"><?php echo $markdown->text($field['description']); ?></div>
                                        <div class="col-12 col-md-<?php echo $field['pattern'] !== '' ? '4' : '1'; ?> ms-auto">
                                            <?php if ($field['pattern'] !== ''): ?><code class="float-end"><?php echo $escape($field['pattern']); ?></code><?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>

                    <section class="module-components mt-5" aria-labelledby="components">
                        <h2 id="components" class="text-success ms-n3"><i class="far fa-wrench fa-fw" aria-hidden="true"></i> Components
                            <a href="#components" class="header-link scroll_to_link" aria-label="Link to components"><span class="fas fa-link" aria-hidden="true"></span></a>
                        </h2>
                        <?php if (!$subworkflow['components']): ?>
                            <p class="text-muted">No components listed.</p>
                        <?php else: ?>
                            <ul class="list-unstyled component-list">
                                <?php foreach ($component_links($subworkflow['components']) as $component): ?>
                                    <li class="border-bottom py-2">
                                        <h4>
                                            <?php if ($component['url']): ?>
                                                <a href="<?php echo $escape($component['url']); ?>"><?php echo $escape($component['name']); ?></a>
                                            <?php else: ?>
                                                <?php echo $escape($component['name']); ?>
                                            <?php endif; ?>
                                        </h4>
                                        <span class="text-muted small"><?php echo $escape(str_replace('https://github.com/', '', $component['remote'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </section>
                </div>
            </article>
        </div>
        <aside class="col-12 col-lg-3 ps-3 h-100">
            <div class="side-sub-subnav">
                <div class="module-sidebar">
                    <div class="row border-bottom">
                        <div class="col-12">
                            <h6 id="pipelines">Pipelines with this subworkflow</h6>
                            <?php if (!$pipelines): ?>
                                <p class="text-muted small">No pipeline usage recorded.</p>
                            <?php else: ?>
                                <?php foreach ($pipelines as $pipeline_name): ?>
                                    <a class="badge bg-light text-dark me-1 mb-1" href="/<?php echo rawurlencode($pipeline_name); ?>"><?php echo $escape($pipeline_name); ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row border-bottom">
                        <div class="col-12 contrib-avatars">
                            <h6 id="authors">Authors</h6>
                            <?php if (!$subworkflow['authors']): ?>
                                <p class="text-muted small">No authors listed.</p>
                            <?php else: ?>
                                <?php foreach ($subworkflow['authors'] as $author): ?>
                                    <a href="https://github.com/<?php echo rawurlencode(ltrim($author, '@')); ?>" title="<?php echo $escape($author); ?>"><img src="https://github.com/<?php echo rawurlencode(ltrim($author, '@')); ?>.png" alt="<?php echo $escape($author); ?>" loading="lazy"></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <h6>get in touch</h6>
                        <p><a class="btn btn-sm btn-outline-secondary" href="https://github.com/sanger-tol/nf-core-modules/issues"><i class="fab fa-github me-1" aria-hidden="true"></i> Open an issue on GitHub</a></p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
    <div class="toast cmd_copied" role="status" aria-live="polite" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Command copied!</strong>
            <button class="ms-2 mb-1 btn-close" data-bs-dismiss="toast" type="button" aria-label="Close"></button>
        </div>
    </div>
<?php include __DIR__ . '/../footer.php';