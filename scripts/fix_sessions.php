<?php
// Script to remove redundant session_start() occurrences in controllers.
$files = [
    __DIR__ . '/../controller/AuthController.php',
    __DIR__ . '/../controller/PreguntaController.php',
    __DIR__ . '/../controller/DashboardController.php',
];

$replacements = [
    // Replace the exact if(session_status...) { session_start(); } blocks with a comment
    '/if\s*\(\s*session_status\(\)\s*===\s*PHP_SESSION_NONE\s*\)\s*\{\s*session_start\(\);\s*\}/s' => "// Session is started in index.php bootstrap",
    // Replace any occurrence of session_start(); followed by session_destroy(); (e.g. logout) with only session_destroy();
    '/session_start\(\);\s*session_destroy\(\);/s' => 'session_destroy();'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Skipped (not found): $file\n";
        continue;
    }
    $content = file_get_contents($file);
    $new = $content;
    foreach ($replacements as $pattern => $rep) {
        $new = preg_replace($pattern, $rep, $new);
    }
    if ($new !== $content) {
        file_put_contents($file, $new);
        echo "Updated: $file\n";
    } else {
        echo "No changes: $file\n";
    }
}

echo "Done.\n";
