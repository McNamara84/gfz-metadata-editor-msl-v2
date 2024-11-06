<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log incoming request details
error_log(sprintf(
    "Incoming request: Method=%s, URI=%s, Script=%s",
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    $_SERVER['SCRIPT_NAME']
));

// Set content type for API responses
header('Content-Type: application/json');

// Set project root directory
$rootDir = dirname(__DIR__);

// Include Composer autoloader
require $rootDir . '/vendor/autoload.php';

// If this is an API request
if (preg_match('#^/api(/v2)?/#', $_SERVER['REQUEST_URI'])) {
    error_log("Handling API request");

    // Set working directory to project root
    chdir($rootDir);

    // Store original REQUEST_URI
    $originalUri = $_SERVER['REQUEST_URI'];

    // Modify REQUEST_URI to work with our API routing
    if (!preg_match('#^/api/v2/#', $_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = '/api/v2' . substr($_SERVER['REQUEST_URI'], 4);
    }

    error_log("Modified REQUEST_URI: " . $_SERVER['REQUEST_URI']);

    try {
        // Special handling for the health check endpoint
        if ($_SERVER['REQUEST_URI'] === '/api/v2/general/alive') {
            echo json_encode(['message' => "I'm still alive..."]);
            exit;
        }

        // Include the API entry point
        require $rootDir . '/api/index.php';
    } catch (Throwable $e) {
        error_log("Error handling API request: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error']);
    }
    exit;
}

// For static files
if (is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Default response for non-API requests
http_response_code(404);
echo json_encode(['error' => 'Not Found']);