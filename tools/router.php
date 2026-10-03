<?php
/**
 * Router untuk server bawaan PHP (php -S), misalnya di MacBook tanpa Apache.
 * Pemakaian dari folder proyek:  php -S localhost:8000 tools/router.php
 * Meniru aturan .htaccess: folder internal dan berkas sensitif tidak boleh diakses.
 */
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$root = dirname(__DIR__);

if (preg_match('#^/(app|config|database|tools|docs|\.devcontainer|\.git)(/|$)#', $path)
    || preg_match('/\.(sql|md|log|ini)$/i', $path)) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

if ($path !== '/' && is_file($root . $path)) {
    return false; // berkas statis (CSS, JS, gambar) dilayani langsung
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
require $root . '/index.php';
