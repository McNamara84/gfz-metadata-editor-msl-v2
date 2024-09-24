<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bestimme die API-Version
$uri = $_SERVER['REQUEST_URI'];
$uriParts = explode('/', trim($uri, '/'));

// Finde den Index von 'api' im URI
$apiIndex = array_search('api', $uriParts);

// Überprüfe, ob eine Version angegeben wurde
if (isset($uriParts[$apiIndex + 1]) && preg_match('/^v\d+$/', $uriParts[$apiIndex + 1])) {
    $version = $uriParts[$apiIndex + 1];
} else {
    // Standardmäßig v2 verwenden, wenn keine Version angegeben wurde
    $version = 'v2';
}

// Pfad zur Versions-spezifischen index.php
$versionFile = __DIR__ . '/' . $version . '/index.php';

// Überprüfe, ob die Datei existiert
if (file_exists($versionFile)) {
    require_once $versionFile;
} else {
    http_response_code(404);
    echo json_encode(['error' => 'API version not found']);
}