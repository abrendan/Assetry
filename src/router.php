<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET'  => [],
    'POST' => [],
];

function route(string $method, string $path, callable $handler): void {
    global $routes;
    $routes[$method][$path] = $handler;
}

function matchRoute(string $method, string $uri): ?array {
    global $routes;
    if (isset($routes[$method][$uri])) {
        return ['handler' => $routes[$method][$uri], 'params' => []];
    }
    foreach ($routes[$method] as $pattern => $handler) {
        if (strpos($pattern, ':') === false) continue;
        $regex = preg_replace('/:[a-zA-Z0-9_]+/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (preg_match($regex, $uri, $matches)) {
            array_shift($matches);
            preg_match_all('/:[a-zA-Z0-9_]+/', $pattern, $paramNames);
            $params = [];
            foreach ($paramNames[0] as $i => $name) {
                $params[ltrim($name, ':')] = $matches[$i];
            }
            return ['handler' => $handler, 'params' => $params];
        }
    }
    return null;
}

// Load all controllers
require_once __DIR__ . '/controllers/auth_controller.php';
require_once __DIR__ . '/controllers/dashboard_controller.php';
require_once __DIR__ . '/controllers/assets_controller.php';
require_once __DIR__ . '/controllers/keys_controller.php';
require_once __DIR__ . '/controllers/licenses_controller.php';
require_once __DIR__ . '/controllers/network_controller.php';
require_once __DIR__ . '/controllers/vault_controller.php';
require_once __DIR__ . '/controllers/admin_controller.php';
require_once __DIR__ . '/controllers/profile_controller.php';
require_once __DIR__ . '/controllers/categories_controller.php';
require_once __DIR__ . '/controllers/data_controller.php';
require_once __DIR__ . '/controllers/types_controller.php';

$match = matchRoute($method, $requestUri);
if ($match) {
    call_user_func($match['handler'], $match['params']);
} else {
    http_response_code(404);
    require __DIR__ . '/views/pages/404.php';
}
