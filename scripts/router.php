<?php

// Router pro servidor embutido do PHP (php -S), usado só em dev local.
// Serve arquivos estáticos direto e manda o resto pro index.php.

$publicPath = dirname(__DIR__) . '/public_html';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = $publicPath . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

require $publicPath . '/index.php';
