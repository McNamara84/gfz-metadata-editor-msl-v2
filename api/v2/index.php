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

/**
 * Load the API routes from the routes configuration file.
 */
$routes = require __DIR__ . '/routes/api.php';

/**
 * Retrieve the HTTP method and URI from the server variables.
 */
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

/**
 * Log the original URI for debugging purposes.
 */
error_log("Original URI: " . $uri);

/**
 * Strip the query string from the URI and decode it.
 */
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

/**
 * Dynamically determine the base path of the script.
 */
$scriptName = $_SERVER['SCRIPT_NAME'];
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$scriptDir .= '/' . $version;

// Debug-Ausgaben NACH der Definition der Variablen
error_log("Requested URI: " . $_SERVER['REQUEST_URI']);
error_log("Script Directory: " . $scriptDir);
error_log("Available routes: " . print_r($routes, true));

/**
 * Remove the base path from the URI to get the relative path.
 */
if (substr($uri, 0, strlen($scriptDir)) === $scriptDir) {
    $uri = substr($uri, strlen($scriptDir));
}

/**
 * Ensure that the URI starts with a '/'.
 */
if (empty($uri) || $uri[0] !== '/') {
    $uri = '/' . $uri;
}

/**
 * Log the processed URI for debugging purposes.
 */
error_log("Processed URI: " . $uri);

/**
 * Initialize the FastRoute dispatcher with the loaded routes.
 */
$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routes) {
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
});

// Debug-Informationen vor dem Dispatching
error_log("Final URI for routing: " . $uri);
error_log("HTTP Method: " . $httpMethod);
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
error_log("Route Info: " . print_r($routeInfo, true));
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        /**
         * Handle the case where no matching route was found.
         */
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        /**
         * Handle the case where the HTTP method is not allowed for the matched route.
         */
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
    case Dispatcher::FOUND:
        /**
         * Handle the case where a matching route was found.
         *
         * @var mixed $handler The handler for the route, which can be a callable or an array specifying a controller and method.
         * @var array $vars    The variables extracted from the URI.
         */
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        if (is_array($handler) && is_object($handler[0])) {
            // If the handler is an array with a controller object and method name.
            $handler[0]->{$handler[1]}($vars);
        } else {
            // If the handler is a callable function.
            call_user_func($handler, $vars);
        }
        break;
}
