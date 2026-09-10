<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../controller/helpers/RasaGenerator.php';

header_remove();

$projectPath = dirname(__DIR__);
$rasaUrl = 'http://localhost:5005';

$result = RasaGenerator::trainAndLoadModel($rasaUrl, $projectPath);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
