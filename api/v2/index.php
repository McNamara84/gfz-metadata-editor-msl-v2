<?php
error_log("Requested URI: " . $_SERVER['REQUEST_URI']);
error_log("Looking for route: " . $uri);
error_log("Available routes: " . print_r($routes, true));
/**
 * Entry point for API version 2.
 *
 * Initializes routing for the API, processes incoming HTTP requests,
 * and dispatches them to the appropriate controllers based on the URI and HTTP method.
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
 *
 * @var array $routes An array of routes defined for the API.
 */
$routes = require __DIR__ . '/routes/api.php';

/**
 * Initialize the FastRoute dispatcher with the loaded routes.
 *
 * @var Dispatcher $dispatcher The dispatcher responsible for routing requests.
 */
$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routes) {
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
});

/**
 * Retrieve the HTTP method and URI from the server variables.
 *
 * @var string $httpMethod The HTTP method of the request (e.g., GET, POST).
 * @var string $uri        The request URI.
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
 *
 * @var string $scriptName The script name from the server variables.
 * @var string $scriptDir  The directory of the script, normalized to use forward slashes.
 */
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /mde-msl/api/index.php
$scriptDir = str_replace('\\', '/', dirname($scriptName)); // e.g., /mde-msl/api

/**
 * Append the API version to the base path.
 */
$scriptDir .= '/' . $version; // Now $scriptDir is e.g., /mde-msl/api/v2

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
 * Dispatch the request to the appropriate route handler.
 *
 * @var array $routeInfo Information about the matched route.
 */
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

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
