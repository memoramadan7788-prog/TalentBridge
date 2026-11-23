<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * Emulate Apache's "mod_rewrite" functionality with PHP built-in server.
 */

$publicPath = __DIR__ . '/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists($publicPath . $uri)) {
    return false;
}

require_once $publicPath . '/index.php';
