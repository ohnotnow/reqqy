#!/usr/bin/env php
<?php

/**
 * Generate .llm.md for a repository
 * Usage: php generate-llm-md.php /path/to/repo
 */

if ($argc < 2) {
    echo "Usage: php generate-llm-md.php /path/to/repo\n";
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
    // check the php version in the .lando.yml if it exists
    if (file_exists("$repoPath/.lando.yml")) {
        $landoYml = file_get_contents("$repoPath/.lando.yml");
        if (preg_match('/php:\s*[\'"]?(\d+\.\d+)[\'"]?/', $landoYml, $matches)) {
            $stack[] = "PHP " . $matches[1];
        }
    } else {
        if (isset($composer['require']['php'])) {
            $stack[] = "PHP " . str_replace('^', '', $composer['require']['php']);
        }
    }

    // Add a few key dependencies
    $keyDeps = ['livewire/livewire', 'livewire/flux', 'livewire/flux-pro'];
    foreach ($keyDeps as $dep) {
        if (isset($composer['require'][$dep])) {
            $stack[] = basename($dep);
        }
    }

    return implode(', ', $stack);
}

// --- 2. Generate filtered directory tree ---
function generateTree($dir, $repoRoot, $prefix = '', $isLast = true, $maxDepth = 4, $currentDepth = 0, $ignorePatterns = null) {
    if ($currentDepth >= $maxDepth) return '';

    // Parse .gitignore from repo root only once
    if ($ignorePatterns === null) {
        $gitignoreFile = "$repoRoot/.gitignore";
        $gitignore = file_exists($gitignoreFile) ? file_get_contents($gitignoreFile) : '';

        if (empty($gitignore)) {
            $gitignore = "vendor\nnode_modules\n.git\n.env\n.env.local\n.env.development.local\n.env.test.local\n.env.production.local";
        }

        $ignorePatterns = explode("\n", $gitignore);
        $ignorePatterns = array_filter($ignorePatterns, function($item) {
            return !empty(trim($item)) && !str_starts_with(trim($item), '#');
        });
        $ignorePatterns = array_map('trim', $ignorePatterns);
        $ignorePatterns = array_filter($ignorePatterns, function($item) {
            return !empty($item);
        });
        $ignorePatterns = array_values($ignorePatterns);
    }

    $output = '';

    $items = array_diff(scandir($dir), ['.', '..']);
    $items = array_filter($items, function($item) use ($dir, $repoRoot, $ignorePatterns) {
        // Skip hidden files/directories
        if (str_starts_with($item, '.')) {
            return false;
        }

        $itemPath = "$dir/$item";
        // Calculate relative path from repo root
        $relativePath = str_replace(rtrim($repoRoot, '/') . '/', '', $itemPath);
        // Handle case where itemPath equals repoRoot exactly
        if ($relativePath === $itemPath) {
            $relativePath = $item;
        }

        // Check if this path matches any gitignore pattern
        foreach ($ignorePatterns as $pattern) {
            if (matchesGitignorePattern($relativePath, $pattern, $repoRoot)) {
                return false;
            }
        }

        return true;
    });
    $items = array_values($items);

    foreach ($items as $index => $item) {
        $path = "$dir/$item";
        $isLastItem = ($index === count($items) - 1);
        $connector = $isLastItem ? '└── ' : '├── ';
        $output .= $prefix . $connector . $item . "\n";

        if (is_dir($path)) {
            $newPrefix = $prefix . ($isLastItem ? '    ' : '│   ');
            $output .= generateTree($path, $repoRoot, $newPrefix, $isLastItem, $maxDepth, $currentDepth + 1, $ignorePatterns);
        }
    }

    return $output;
}

// Helper function to check if a path matches a gitignore pattern
function matchesGitignorePattern($relativePath, $pattern, $repoRoot): bool {
    // Check if pattern starts with / (it means "root only")
    $isRootOnly = str_starts_with($pattern, '/');
    if ($isRootOnly) {
        $pattern = substr($pattern, 1);
        // Check if it matches at root level
        if (str_starts_with($relativePath, $pattern . '/') || $relativePath === $pattern) {
            return true;
        }
        // Also check if it's a directory name at root
        $pathParts = explode('/', $relativePath);
        if (isset($pathParts[0]) && $pathParts[0] === $pattern) {
            return true;
        }
        return false;
    }

    // Simple exact match (for directories like "vendor", "node_modules")
    if ($pattern === basename($relativePath) || $pattern === $relativePath) {
        return true;
    }

    // Check if pattern matches any part of the path
    if (strpos($relativePath, $pattern) !== false) {
        // Check if it's a directory name (not just part of a filename)
        $pathParts = explode('/', $relativePath);
        foreach ($pathParts as $part) {
            if ($part === $pattern) {
                return true;
            }
        }
    }

    return false;
}

// --- 3. Extract test names ---
function getTestFeatures($repoPath, $useLlm = false) {
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

            // do the same for Pest style tests
            preg_match_all('/it\([\'"](.*?)[\'"],\s*function\s*\(\)\s*\{/s', $content, $pestMatches);
            if (!empty($pestMatches[1])) {
                foreach ($pestMatches[1] as $testName) {
                    $feature = ucfirst($testName);
                    $features[] = $feature;
                }
            }
        }
    }

    $featureList = implode("\n", array_unique($features));

    if ($useLlm) {
        // call out to openai with this prompt:
        $prompt = <<<PROMPT
You are analyzing test names from a Laravel application to identify core features.

Filter this list to include ONLY tests that describe:
- Primary entities and resources (e.g., "can create a project")
- User-facing workflows and business processes
- Key integrations or automation
- Important domain concepts

EXCLUDE tests that are:
- Validation rules (required fields, format checks, length limits)
- Edge cases and error handling
- Authorization/permission checks
- Empty state displays
- Form state management (resets, initialization)
- Relationship eager loading or query optimization

Keep ONE representative CRUD test per entity to show what exists in the system.
For other tests, only include those that reveal business logic or workflows.

Rewrite kept tests as concise feature statements.

Here's the test list:

[test-list]
{$featureList}
[/test-list]

## Response format

Output ONLY the filtered feature list. Do not include any introduction, explanation, or follow-up questions.
PROMPT;

        $apiKey = getenv('OPENAI_API_KEY');

        if (empty($apiKey)) {
            echo "Warning: OPENAI_API_KEY not set, skipping LLM filtering\n";
            return $featureList;
        }

        $ch = curl_init('https://api.openai.com/v1/responses');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'gpt-5-nano',
                'input' => $prompt,
                "reasoning" => ["effort" => "low"],
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo "Warning: Curl error - $curlError\n";
            return $featureList;
        }

        if ($httpCode !== 200) {
            echo "Warning: OpenAI API returned HTTP $httpCode\n";
            echo "Response: $response\n";
            return $featureList;
        }

        $data = json_decode($response, true);

        if (!$data || !isset($data['output'])) {
            echo "Warning: Unexpected response format from OpenAI API\n";
            return $featureList;
        }

        // Find the message output (skip reasoning output)
        $messageOutput = null;
        foreach ($data['output'] as $output) {
            if ($output['type'] === 'message' && $output['status'] === 'completed') {
                $messageOutput = $output;
                break;
            }
        }

        if (!$messageOutput || !isset($messageOutput['content'][0]['text'])) {
            echo "Warning: Could not find message content in OpenAI response\n";
            return $featureList;
        }

        $filteredFeatures = $messageOutput['content'][0]['text'];
        return $filteredFeatures;
    }
    return $featureList;
}

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
$output = "# Repository Overview\n\n";
$output .= "> Auto-generated by generate-llm-md.php\n\n";

$output .= "## Purpose\n\n";
$output .= summarizeReadme($repoPath) . "\n\n";

$output .= "## Tech Stack\n\n";
$output .= getTechStack($repoPath) . "\n\n";

$output .= "## Directory Structure\n\n";
$output .= "```\n";
$output .= generateTree($repoPath, $repoPath);
$output .= "```\n\n";

$output .= "## Key Entry Points\n\n";
$entryPoints = getEntryPoints($repoPath);
foreach ($entryPoints as $ep) {
    $output .= "- `$ep`\n";
}
$output .= "\n";

$output .= "## Features (from tests)\n\n";
$features = getTestFeatures($repoPath, true);
$output .= $features . "\n\n";

// Write to file
file_put_contents("$repoPath/.llm.md", $output);
echo "✓ Created {$repoPath}/.llm.md\n";
