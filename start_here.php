#!/usr/bin/env php
<?php

/**
 * Generate START_HERE.md for a repository
 * Usage: php generate-start-here.php /path/to/repo
 */

if ($argc < 2) {
    echo "Usage: php generate-start-here.php /path/to/repo\n";
    exit(1);
}

$repoPath = rtrim($argv[1], '/');

if (!is_dir($repoPath)) {
    echo "Error: Directory not found: $repoPath\n";
    exit(1);
}

// --- 1. Get tech stack from composer.json ---
function getTechStack($repoPath) {
    $composerFile = "$repoPath/composer.json";
    if (!file_exists($composerFile)) {
        return "No composer.json found";
    }

    $composer = json_decode(file_get_contents($composerFile), true);
    $stack = [];

    if (isset($composer['require']['laravel/framework'])) {
        $stack[] = "Laravel " . str_replace('^', '', $composer['require']['laravel/framework']);
    }
    if (isset($composer['require']['php'])) {
        $stack[] = "PHP " . str_replace('^', '', $composer['require']['php']);
    }

    // Add a few key dependencies
    $keyDeps = ['inertiajs/inertia-laravel', 'livewire/livewire', 'spatie/laravel-permission'];
    foreach ($keyDeps as $dep) {
        if (isset($composer['require'][$dep])) {
            $stack[] = basename($dep);
        }
    }

    return implode(', ', $stack);
}

// --- 2. Generate filtered directory tree ---
function generateTree($dir, $prefix = '', $isLast = true, $maxDepth = 3, $currentDepth = 0) {
    if ($currentDepth >= $maxDepth) return '';

    $ignore = ['vendor', 'node_modules', 'storage', '.git', 'public/build', 'bootstrap/cache'];
    $output = '';

    $items = array_diff(scandir($dir), ['.', '..']);
    $items = array_filter($items, function($item) use ($ignore) {
        return !in_array($item, $ignore) && !str_starts_with($item, '.');
    });
    $items = array_values($items);

    foreach ($items as $index => $item) {
        $path = "$dir/$item";
        $isLastItem = ($index === count($items) - 1);
        $connector = $isLastItem ? '└── ' : '├── ';
        $output .= $prefix . $connector . $item . "\n";

        if (is_dir($path)) {
            $newPrefix = $prefix . ($isLastItem ? '    ' : '│   ');
            $output .= generateTree($path, $newPrefix, $isLastItem, $maxDepth, $currentDepth + 1);
        }
    }

    return $output;
}

// --- 3. Extract test names ---
function getTestFeatures($repoPath) {
    $testsDir = "$repoPath/tests/Feature";
    if (!is_dir($testsDir)) {
        return ["No feature tests found"];
    }

    $features = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testsDir));

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());

            // Match test method names
            preg_match_all('/public function (test_[a-zA-Z0-9_]+)/', $content, $matches);
            foreach ($matches[1] as $testName) {
                // Convert test_user_can_export_to_csv to "User can export to CSV"
                $feature = str_replace('test_', '', $testName);
                $feature = str_replace('_', ' ', $feature);
                $feature = ucfirst($feature);
                $features[] = $feature;
            }
        }
    }

    return array_unique($features);
}

// --- 4. Summarize README with LLM (placeholder) ---
function summarizeReadme($repoPath) {
    $readmeFile = "$repoPath/README.md";
    if (!file_exists($readmeFile)) {
        return "No README found";
    }

    $readmeContent = file_get_contents($readmeFile);

    // TODO: Call your LLM API here
    // For now, return the content with instructions

    /*
     * Example prompt for Claude Haiku (or similar):
     *
     * "You are helping create concise documentation for a code repository.
     *
     * Please read the following README and provide a 2-3 sentence summary that covers:
     * 1. What this application does (its main purpose)
     * 2. Who the primary users are (if mentioned)
     * 3. The key problem it solves
     *
     * Keep it factual and concise. Do not include installation instructions,
     * technical details, or contribution guidelines.
     *
     * README:
     * ---
     * {$readmeContent}
     * ---
     *
     * Summary:"
     *
     * Example API call:
     * $summary = callClaudeAPI([
     *     'model' => 'claude-haiku-4-20250514',
     *     'max_tokens' => 200,
     *     'messages' => [[
     *         'role' => 'user',
     *         'content' => $prompt
     *     ]]
     * ]);
     */

    // For now, just return first 500 chars as fallback
    return substr($readmeContent, 0, 500) . "...\n\n[TODO: Wire up LLM summarization]";
}

// --- 5. Get key entry points ---
function getEntryPoints($repoPath) {
    $routes = [];
    $routesFile = "$repoPath/routes/web.php";

    if (file_exists($routesFile)) {
        $routes[] = "routes/web.php";
    }
    if (file_exists("$repoPath/routes/api.php")) {
        $routes[] = "routes/api.php";
    }

    return $routes;
}

// --- Generate the START_HERE.md ---
echo "Generating START_HERE.md for: $repoPath\n";

$output = "# Repository Overview\n\n";
$output .= "> Auto-generated by generate-start-here.php\n\n";

$output .= "## Purpose\n\n";
$output .= summarizeReadme($repoPath) . "\n\n";

$output .= "## Tech Stack\n\n";
$output .= getTechStack($repoPath) . "\n\n";

$output .= "## Directory Structure\n\n";
$output .= "```\n";
$output .= generateTree($repoPath);
$output .= "```\n\n";

$output .= "## Key Entry Points\n\n";
$entryPoints = getEntryPoints($repoPath);
foreach ($entryPoints as $ep) {
    $output .= "- `$ep`\n";
}
$output .= "\n";

$output .= "## Features (from tests)\n\n";
$features = getTestFeatures($repoPath);
foreach ($features as $feature) {
    $output .= "- $feature\n";
}

// Write to file
file_put_contents("$repoPath/START_HERE.md", $output);
echo "✓ Created START_HERE.md\n";
