<?php
error_log('Request URI: ' . $_SERVER["REQUEST_URI"]);

// If the request is for a static file that exists, serve it directly
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

// Set the project root path
$root = dirname(__DIR__);

// For API requests
if (preg_match('/^\/api/', $_SERVER['REQUEST_URI'])) {
    error_log('Handling API request');
    // Set working directory to project root
    chdir($root);
    // Include the main API entry point
    require $root . '/api/index.php';
    return true;
}

error_log('Falling back to main index.php');
// For all other requests
require $root . '/index.php';