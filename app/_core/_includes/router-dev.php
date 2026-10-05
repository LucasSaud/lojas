<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$file = realpath(__DIR__ . '/../../' . ltrim($uri, '/'));

if ($uri !== '/' && $file !== false) {
    return false;
}

$_GET['insubdominio'] = isset($_GET['insubdominio']) ? $_GET['insubdominio'] : '';
$_GET['inrouter'] = ltrim($uri, '/');

require __DIR__ . '/../../index.php';

return true;