<?php
require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

function subworkflow_github_query($url, $config, $allow_missing = false) {
    $headers = ['User-Agent: sanger-tol-pipelines-website', 'Accept: application/vnd.github+json'];
    if (!empty($config['github_access_token'])) {
        $headers[] = 'Authorization: Basic ' . base64_encode(
            $config['github_username'] . ':' . $config['github_access_token'],
        );
    }
    $context = stream_context_create(['http' => [
        'method' => 'GET',
        'header' => $headers,
        'timeout' => 30,
        'ignore_errors' => true,
    ]]);
    $body = @file_get_contents($url, false, $context);
    $status = $http_response_header[0] ?? '';
    if ($allow_missing && preg_match('/\s404\s/', $status)) {
        return null;
    }
    if ($body === false || !preg_match('/\s200\s/', $status)) {
        throw new RuntimeException('Could not fetch ' . $url . ' (' . $status . ')');
    }
    $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new RuntimeException('Invalid GitHub response for ' . $url);
    }
    return $data;
}

function subworkflow_blob_content($blob) {
    if (($blob['encoding'] ?? '') !== 'base64' || !isset($blob['content'])) {
        throw new RuntimeException('Missing base64 content in GitHub response');
    }
    $content = base64_decode($blob['content'], true);
    if ($content === false) {
        throw new RuntimeException('Invalid base64 content in GitHub response');
    }
    return $content;
}

function update_subworkflow_details($conn, callable $query) {
    $tree = $query('https://api.github.com/repos/sanger-tol/nf-core-modules/git/trees/main?recursive=1');
    if (!isset($tree['tree']) || !is_array($tree['tree']) || !empty($tree['truncated'])) {
        throw new RuntimeException('GitHub returned an incomplete repository tree');
    }
    $subworkflows = [];
    foreach ($tree['tree'] as $file) {
        if (substr($file['path'], -8) == 'meta.yml' && substr($file['path'], 0, 13) == 'subworkflows/') {
            $blob = $query($file['url']);
            $metadata = Yaml::parse(subworkflow_blob_content($blob));
            if (!is_array($metadata) || empty($metadata['name']) || !is_string($metadata['name'])) {
                throw new RuntimeException('Missing subworkflow name in ' . $file['path']);
            }
            foreach (['input', 'output', 'components'] as $field) {
                if (isset($metadata[$field]) && !is_array($metadata[$field])) {
                    throw new RuntimeException('Invalid ' . $field . ' in ' . $file['path']);
                }
                $metadata[$field] = json_encode($metadata[$field] ?? [], JSON_THROW_ON_ERROR);
            }
            foreach (['keywords', 'authors'] as $field) {
                $value = $metadata[$field] ?? [];
                $metadata[$field] = is_array($value) ? implode(';', $value) : $value;
            }
            $metadata['github_sha'] = $blob['sha'];
            $metadata['api_url'] = $file['url'];
            $metadata['github_path'] = $file['path'];
            $subworkflows[] = $metadata;
        }
    }

    $conn->query("CREATE TABLE IF NOT EXISTS nfcore_suworkflows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        github_sha VARCHAR(400) NOT NULL,
        github_path VARCHAR(400) NOT NULL,
        api_url VARCHAR(400) NOT NULL,
        name VARCHAR(400) NOT NULL,
        description VARCHAR(4000) DEFAULT NULL,
        keywords VARCHAR(2000) DEFAULT NULL,
        input JSON NOT NULL,
        output JSON NOT NULL,
        authors VARCHAR(2000) NOT NULL,
        components JSON NOT NULL,
        date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY subworkflow_path (github_path)
    ) ENGINE=InnoDB");
    $conn->query("CREATE TABLE IF NOT EXISTS pipeline_subworkflows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pipeline_id INT NOT NULL,
        subworkflow_id INT NOT NULL,
        UNIQUE KEY pipeline_subworkflow (pipeline_id, subworkflow_id),
        FOREIGN KEY (pipeline_id) REFERENCES nfcore_pipelines(id),
        FOREIGN KEY (subworkflow_id) REFERENCES nfcore_suworkflows(id)
    ) ENGINE=InnoDB");
    $statement = $conn->prepare('INSERT INTO nfcore_suworkflows
        (github_sha, github_path, api_url, name, description, keywords, input, output, authors, components)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE github_sha = VALUES(github_sha), api_url = VALUES(api_url),
        name = VALUES(name), description = VALUES(description), keywords = VALUES(keywords),
        input = VALUES(input), output = VALUES(output), authors = VALUES(authors), components = VALUES(components)');
    $conn->begin_transaction();
    try {
        foreach ($subworkflows as $subworkflow) {
            $description = $subworkflow['description'] ?? null;
            $statement->bind_param(
                'ssssssssss',
                $subworkflow['github_sha'],
                $subworkflow['github_path'],
                $subworkflow['api_url'],
                $subworkflow['name'],
                $description,
                $subworkflow['keywords'],
                $subworkflow['input'],
                $subworkflow['output'],
                $subworkflow['authors'],
                $subworkflow['components'],
            );
            $statement->execute();
        }
        $paths = array_column($subworkflows, 'github_path');
        $stale_condition = $paths
            ? 'github_path NOT IN (' . implode(', ', array_fill(0, count($paths), '?')) . ')'
            : '1 = 1';
        foreach ([
            'DELETE pipeline_subworkflows FROM pipeline_subworkflows
                INNER JOIN nfcore_suworkflows ON pipeline_subworkflows.subworkflow_id = nfcore_suworkflows.id
                WHERE ' . $stale_condition,
            'DELETE FROM nfcore_suworkflows WHERE ' . $stale_condition,
        ] as $delete_sql) {
            $delete_statement = $conn->prepare($delete_sql);
            if ($paths) {
                $delete_statement->bind_param(str_repeat('s', count($paths)), ...$paths);
            }
            $delete_statement->execute();
            $delete_statement->close();
        }
        $conn->commit();
    } catch (Throwable $error) {
        $conn->rollback();
        throw $error;
    }
    $statement->close();
    echo 'Saved ' . count($subworkflows) . " subworkflows.\n";

    $subworkflow_ids = [];
    $result = $conn->query('SELECT id, github_path FROM nfcore_suworkflows');
    while ($row = $result->fetch_assoc()) {
        $subworkflow_ids[strtolower($row['github_path'])] = $row['id'];
    }
    $result->free();
    $pipelines = $conn->query("SELECT id, name FROM nfcore_pipelines WHERE pipeline_type = 'pipelines'")->fetch_all(MYSQLI_ASSOC);
    $delete = $conn->prepare('DELETE FROM pipeline_subworkflows WHERE pipeline_id = ?');
    $insert = $conn->prepare('INSERT INTO pipeline_subworkflows (pipeline_id, subworkflow_id) VALUES (?, ?)');
    $failed_pipelines = 0;
    foreach ($pipelines as $pipeline) {
        try {
            $blob = $query(
                'https://api.github.com/repos/sanger-tol/' . rawurlencode($pipeline['name']) . '/contents/modules.json',
                true,
            );
            if ($blob === null) {
                echo 'No modules.json for ' . $pipeline['name'] . "; keeping existing relationships.\n";
                continue;
            }
            $manifest = json_decode(subworkflow_blob_content($blob), true, 512, JSON_THROW_ON_ERROR);
            if (!isset($manifest['repos']) || !is_array($manifest['repos'])) {
                throw new RuntimeException('Invalid modules.json');
            }
            $linked_ids = [];
            foreach ($manifest['repos'] as $remote => $repository) {
                if (!in_array(rtrim($remote, '/'), [
                    'https://github.com/sanger-tol/nf-core-modules.git',
                    'https://github.com/sanger-tol/nf-core-modules',
                ], true)) {
                    continue;
                }
                $installed = $repository['subworkflows']['sanger-tol'] ?? [];
                if (!is_array($installed)) {
                    throw new RuntimeException('Invalid subworkflow list');
                }
                foreach ($installed as $name => $details) {
                    $path = strtolower('subworkflows/sanger-tol/' . $name . '/meta.yml');
                    if (!isset($subworkflow_ids[$path])) {
                        throw new RuntimeException('Unknown subworkflow: ' . $name);
                    }
                    $linked_ids[$subworkflow_ids[$path]] = $subworkflow_ids[$path];
                }
            }
            $conn->begin_transaction();
            try {
                $delete->bind_param('i', $pipeline['id']);
                $delete->execute();
                foreach ($linked_ids as $subworkflow_id) {
                    $insert->bind_param('ii', $pipeline['id'], $subworkflow_id);
                    $insert->execute();
                }
                $conn->commit();
            } catch (Throwable $error) {
                $conn->rollback();
                throw $error;
            }
            echo 'Linked ' . count($linked_ids) . ' subworkflows to ' . $pipeline['name'] . ".\n";
        } catch (Throwable $error) {
            $failed_pipelines++;
            echo 'Could not update ' . $pipeline['name'] . ': ' . $error->getMessage() . "\n";
        }
    }
    $delete->close();
    $insert->close();
    return $failed_pipelines;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if (PHP_SAPI !== 'cli') {
        http_response_code(403);
        exit('Run this update from the command line.');
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $config = parse_ini_file(__DIR__ . '/config.ini');
        $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname'], $config['port']);
        $conn->set_charset('utf8mb4');
        echo 'Updating subworkflow details - ' . date('Y-m-d H:i:s') . "\n";
        $failed_pipelines = update_subworkflow_details($conn, function ($url, $allow_missing = false) use ($config) {
            return subworkflow_github_query($url, $config, $allow_missing);
        });
        $conn->close();
        echo "Subworkflow update complete.\n";
        exit($failed_pipelines ? 1 : 0);
    } catch (Throwable $error) {
        fwrite(STDERR, 'ERROR: ' . $error->getMessage() . "\n");
        exit(1);
    }
}