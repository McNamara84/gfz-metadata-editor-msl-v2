<?php
/**
 * Entry point for API version 2.
 */

// Define the API version.
$version = 'v2';

/**
 * Include necessary dependencies and controller classes.
 */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/controllers/GeneralController.php';
require_once __DIR__ . '/controllers/VocabController.php';
require_once __DIR__ . '/controllers/DatasetController.php';
require_once __DIR__ . '/controllers/ValidationController.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

// Debug-Ausgaben für den Request
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
error_log("PHP_SELF: " . $_SERVER['PHP_SELF']);

/**
 * Load the API routes from the routes configuration file.
 */
$routes = require __DIR__ . '/routes/api.php';

// Debug-Ausgabe für geladene Routen
error_log("Loaded routes:");
foreach ($routes as $route) {
    error_log("Route: " . $route[0] . " " . $route[1]);
}

/**
 * Process the URI
 */
$uri = $_SERVER['REQUEST_URI'];
error_log("Original URI: " . $uri);

// Strip query string and decode
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Extract the path after /api/v2/
if (preg_match('#^.*?/api/v2(/.*)?$#', $uri, $matches)) {
    $uri = $matches[1] ?? '/';
} else {
    $uri = '/';
}

error_log("Processed URI for routing: " . $uri);

/**
 * Initialize the FastRoute dispatcher
 */
$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routes) {
    foreach ($routes as $route) {
        error_log("Adding route: " . $route[0] . " " . $route[1]);
        $r->addRoute($route[0], $route[1], $route[2]);
    }
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

error_log("Route dispatch result: " . print_r($routeInfo, true));

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        error_log("Route not found for URI: " . $uri);
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        error_log("Method not allowed: " . $httpMethod . " for URI: " . $uri);
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;

    case Dispatcher::FOUND:
        error_log("Route found for URI: " . $uri);
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        try {
            if (is_array($handler) && is_object($handler[0])) {
                error_log("Executing controller method: " . get_class($handler[0]) . "::" . $handler[1]);
                $handler[0]->{$handler[1]}($vars);
            } else {
                error_log("Executing callable handler");
                call_user_func($handler, $vars);
            }
        } catch (Exception $e) {
            error_log("Error executing handler: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error']);
        }
        break;
}