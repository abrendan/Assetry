<?php
// When using PHP's built-in server, serve static files directly
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) {
        return false; // Let the built-in server handle it natively
    }
}

// Front controller — route all requests through the router
require_once __DIR__ . '/../src/router.php';
