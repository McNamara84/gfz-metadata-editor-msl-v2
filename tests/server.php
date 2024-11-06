<?php
// If the request is for a static file that exists, serve it directly
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

// Set the project root path
$root = dirname(__DIR__);

// For API requests
if (preg_match('/^\/api/', $_SERVER['REQUEST_URI'])) {
    // Include the main API entry point
    require $root . '/api/index.php';
    return true;
}

// For all other requests
require $root . '/index.php';