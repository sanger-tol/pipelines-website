<?php
require_once __DIR__ . '/../includes/functions.php';

$escape = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$update_output = [];
$update_status = null;
if (!isset($subworkflow_name) && isset($_GET['update'])) {
    exec('php ' . escapeshellarg(__DIR__ . '/../update_subworkflow_details.php') . ' 2>&1', $update_output, $update_status);
}
$markdown = new ParsedownExtra();
$markdown->setSafeMode(true);
$subworkflows = [];
$modules = [];
$pipeline_links = [];
$load_error = false;
$conn = null;
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $config = parse_ini_file(__DIR__ . '/../config.ini');
    $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname'], $config['port']);
    $conn->set_charset('utf8mb4');
    $result = $conn->query('SELECT * FROM nfcore_suworkflows ORDER BY LOWER(name)');
    while ($row = $result->fetch_assoc()) {
        foreach (['components', 'input', 'output'] as $field) {
            $row[$field] = json_decode($row[$field], true, 512, JSON_THROW_ON_ERROR) ?? [];
        }
        foreach (['keywords', 'authors'] as $field) {
            $row[$field] = array_values(array_filter(array_map('trim', explode(';', $row[$field] ?? ''))));
        }
        $subworkflows[] = $row;
    }
    $result->free();
    if ($subworkflows) {
        $modules = $conn->query('SELECT name, github_path FROM nfcore_modules')->fetch_all(MYSQLI_ASSOC);
        $result = $conn->query('SELECT pipeline_subworkflows.subworkflow_id, nfcore_pipelines.name
            FROM pipeline_subworkflows
            INNER JOIN nfcore_pipelines ON pipeline_subworkflows.pipeline_id = nfcore_pipelines.id
            ORDER BY LOWER(nfcore_pipelines.name)');
        while ($row = $result->fetch_assoc()) {
            $pipeline_links[$row['subworkflow_id']][] = $row['name'];
        }
        $result->free();
    }
} catch (mysqli_sql_exception $error) {
    $load_error = $error->getCode() !== 1146 || count($subworkflows) > 0;
} catch (Throwable $error) {
    $load_error = true;
}
if ($conn) {
    $conn->close();
}
if ($load_error) {
    http_response_code(503);
}

$resource_links = [];
foreach ($modules as $module) {
    $resource_links[strtolower($module['github_path'])] = '/modules/' . rawurlencode($module['name']);
}
foreach ($subworkflows as $subworkflow) {
    $resource_links[strtolower($subworkflow['github_path'])] = '/subworkflows/' . rawurlencode($subworkflow['name']);
}
$component_links = function ($components) use ($resource_links) {
    $links = [];
    foreach ($components as $component) {
        $entries = is_string($component) ? [$component => []] : $component;
        foreach ($entries as $name => $options) {
            $remote = rtrim($options['git_remote'] ?? 'https://github.com/sanger-tol/nf-core-modules.git', '/');
            $remote = preg_replace('/\.git$/', '', $remote);
            if ($remote === 'https://github.com/sanger-tol/nf-core-modules') {
                $module_path = 'modules/sanger-tol/' . $name . '/meta.yml';
                $subworkflow_path = 'subworkflows/sanger-tol/' . $name . '/meta.yml';
                $url = $resource_links[strtolower($module_path)] ?? $resource_links[strtolower($subworkflow_path)] ??
                    $remote . '/tree/main/modules/sanger-tol/' . implode('/', array_map('rawurlencode', explode('/', $name)));
            } elseif ($remote === 'https://github.com/nf-core/modules') {
                $url = 'https://nf-co.re/modules/' . rawurlencode(str_replace('/', '_', $name));
            } elseif (preg_match('~^https://github\.com/[\w.-]+/[\w.-]+$~', $remote)) {
                $url = $remote . '/search?q=' . rawurlencode($name);
            } else {
                $url = null;
            }
            $links[] = ['name' => $name, 'url' => $url, 'remote' => $remote];
        }
    }
    return $links;
};

if (isset($subworkflow_name) && !$load_error) {
    foreach ($subworkflows as $subworkflow) {
        if (strcasecmp($subworkflow['name'], $subworkflow_name) === 0) {
            include __DIR__ . '/../includes/subworkflow_page/_index.php';
            return;
        }
    }
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$keyword = isset($_GET['keyword']) && is_string($_GET['keyword']) ? $_GET['keyword'] : '';
$keywords = [];
$visible_subworkflows = [];
foreach ($subworkflows as $subworkflow) {
    foreach ($subworkflow['keywords'] as $tag) {
        $keywords[$tag] = $keywords[$tag] ?? 0;
    }
    $search_text = $subworkflow['name'] . ' ' . $subworkflow['description'] . ' ' . implode(' ', $subworkflow['keywords']);
    foreach ($component_links($subworkflow['components']) as $component) {
        $search_text .= ' ' . $component['name'];
    }
    if (($search === '' || stripos($search_text, $search) !== false) &&
        ($keyword === '' || in_array($keyword, $subworkflow['keywords'], true))) {
        $visible_subworkflows[] = $subworkflow;
        foreach (array_unique($subworkflow['keywords']) as $tag) {
            $keywords[$tag]++;
        }
    }
}
arsort($keywords);
$keyword_url = function ($tag) use ($search, $keyword) {
    return '/subworkflows?' . http_build_query([
        'q' => $search,
        'keyword' => (string) $tag === $keyword ? '' : $tag,
    ]);
};
$title = 'Subworkflows';
$subtitle = 'Browse the <strong>' . count($subworkflows) . '</strong> subworkflows from the sanger-tol/nf-core-modules repository.';
include __DIR__ . '/../includes/header.php';
?>
<style>
    .subworkflow-list { overflow-wrap: anywhere; }
    .subworkflow-list .badge { white-space: normal; text-align: left; }
    .subworkflow-list .subworkflow-filters { width: 100%; max-width: 28rem; }
    .subworkflow-facets .facet-value { align-self: start; }
</style>
<div class="subworkflow-list">
    <?php if ($update_status !== null): ?>
        <div class="alert alert-<?php echo $update_status === 0 ? 'success' : 'danger'; ?>" role="status">
            <?php echo $update_status === 0 ? 'Subworkflow update completed.' : 'Subworkflow update failed or completed with errors.'; ?>
            <pre class="mb-0"><?php echo $escape(implode("\n", $update_output)); ?></pre>
        </div>
    <?php endif; ?>
    <h1>Available Subworkflows</h1>
    <p class="mb-3">Subworkflows combine modules into reusable groups of processes for Nextflow DSL2 pipelines. These subworkflows are maintained in the <a href="https://github.com/sanger-tol/nf-core-modules">sanger-tol/nf-core-modules</a> repository. For guidance on writing subworkflows, see the <a href="https://nf-co.re/docs">nf-core documentation</a>.</p>
    <form action="/subworkflows" method="get" class="btn-toolbar mb-4" role="search">
        <div class="input-group input-group-sm subworkflow-filters">
            <label for="subworkflow-search" class="visually-hidden">Search subworkflows</label>
            <input id="subworkflow-search" type="search" name="q" class="form-control" placeholder="Search subworkflows" value="<?php echo $escape($search); ?>">
            <input type="hidden" name="keyword" value="<?php echo $escape($keyword); ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search me-1" aria-hidden="true"></i>Search</button>
        </div>
    </form>
    <?php if ($load_error): ?>
        <p class="alert alert-warning">Subworkflows are temporarily unavailable. Please try again later.</p>
    <?php elseif (!$subworkflows): ?>
        <p class="text-muted">No subworkflows have been imported yet.</p>
    <?php else: ?>
        <div class="row flex-wrap-reverse flex-lg-wrap me-lg-5">
            <aside class="col-12 col-lg-3 pe-2" aria-label="Filter by keyword">
                <div class="subworkflow-facets">
                    <ul class="list-unstyled">
                        <?php foreach ($keywords as $tag => $count): ?>
                            <li class="facet-item<?php echo (string) $tag === $keyword ? ' active' : ''; ?>">
                                <a class="d-flex justify-content-between gap-2 text-decoration-none <?php echo (string) $tag === $keyword ? 'text-success' : 'text-reset'; ?>" href="<?php echo $escape($keyword_url($tag)); ?>"<?php echo (string) $tag === $keyword ? ' aria-current="true"' : ''; ?>>
                                    <span class="facet-name"><?php echo $escape($tag); ?></span>
                                    <span class="facet-value badge rounded-pill flex-shrink-0 <?php echo (string) $tag === $keyword ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $count; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
            <div class="col-12 col-lg-9">
                <?php if (!$visible_subworkflows): ?>
                    <p class="text-muted">No subworkflows match your filters. <a href="/subworkflows">Clear filters</a></p>
                <?php else: ?>
                    <p class="text-muted"><?php echo count($visible_subworkflows); ?> subworkflows<?php if ($search !== '' || $keyword !== ''): ?>; <a href="/subworkflows">Clear filters</a><?php endif; ?></p>
                    <div class="modules-container-list subworkflows-container">
                        <?php foreach ($visible_subworkflows as $subworkflow): ?>
                            <article class="card pipeline module mb-2 subworkflow-card">
                                <div class="card-body">
                                    <div class="module-name">
                                        <h4 class="card-title mb-0"><a class="pipeline-name" href="/subworkflows/<?php echo rawurlencode($subworkflow['name']); ?>"><?php echo $escape($subworkflow['name']); ?></a></h4>
                                    </div>
                                    <div class="subworkflow-topics mb-0">
                                        <?php foreach (array_unique($subworkflow['keywords']) as $tag): ?>
                                            <a class="badge pipeline-topic" href="<?php echo $escape($keyword_url($tag)); ?>"><?php echo $escape($tag); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="card-text mb-0 mt-2 description"><?php echo $markdown->text($subworkflow['description'] ?? ''); ?></div>
                                    <p class="small text-muted mb-0"><?php echo count($subworkflow['components']); ?> components; <?php echo count($pipeline_links[$subworkflow['id']] ?? []); ?> pipelines</p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <p class="mt-5 small text-muted">
        <a href="/subworkflows?update">Click here</a> to trigger an update.
    </p>
</div>
<?php include __DIR__ . '/../includes/footer.php';