<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/controllers/GeneralController.php';
require_once __DIR__ . '/controllers/VocabController.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

$routes = require __DIR__ . '/routes/api.php';

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routes) {
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Remove base path
$basePath = '/mde-msl/api/v2';
$uri = substr($uri, strlen($basePath));

// Ensure the URI starts with a /
if (empty($uri) || $uri[0] !== '/') {
    $uri = '/' . $uri;
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        if (is_array($handler) && is_object($handler[0])) {
            $handler[0]->{$handler[1]}($vars);
        } else {
            call_user_func($handler, $vars);
        }
        break;
}
