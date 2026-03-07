<?php
$page = preg_replace('/[^A-Za-z0-9_]/', '', $_GET['page'] ?? '');
$file = __DIR__ . '/content/' . $page . '.md';
if (!$page || !file_exists($file)) {
    http_response_code(404);
    echo 'Page not found.';
    exit;
}

require __DIR__ . '/includes/_markdown.php';
$markdown = file_get_contents($file);

// Extract title from first heading
$title = $page;
if (preg_match('/^#\s+(.+)$/m', $markdown, $m)) {
    $title = trim($m[1]);
}

include __DIR__ . '/includes/_header.php';
echo markdown_to_html($markdown);
include __DIR__ . '/includes/_footer.php';
