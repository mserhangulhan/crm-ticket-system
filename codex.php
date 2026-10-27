<?php

require_once __DIR__ . '/src/CodexReviewer.php';

use Crm\Ticket\CodexReviewer;

$action = $argv[1] ?? 'help';

if ($action === 'review') {
    $diff = shell_exec('git diff HEAD~1 HEAD 2>/dev/null') ?: shell_exec('git diff --staged 2>/dev/null') ?: '';
    
    if (empty(trim($diff))) {
        echo "No changes detected to review.\n";
        exit(0);
    }

    echo "Running Codex PR review on git changes...\n";
    $reviewer = new CodexReviewer();
    $review = $reviewer->reviewDiff($diff);
    echo "\n=== CODEX REVIEW RESULTS ===\n";
    echo $review . "\n";
    echo "=============================\n";
    exit(0);
}

echo "CRM Ticket System - Codex Review CLI\n";
echo "Usage:\n";
echo "  php codex.php review      Analyze recent git diff using Codex / OpenAI API\n";
