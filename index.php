<?php
/**
 * SIAKAD - Sistem Informasi Perkuliahan
 * Front controller: semua permintaan masuk lewat file ini.
 * Format URL: index.php?page=<halaman>&action=<aksi>&id=<id>
 */

declare(strict_types=1);

define('BASE_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_name('SIAKADSESSID');
session_start();

require APP_PATH . '/core/helpers.php';

spl_autoload_register(function (string $class): void {
    foreach (['core', 'controllers', 'models'] as $dir) {
        $file = APP_PATH . "/$dir/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

date_default_timezone_set(config('timezone'));
if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Pesan galat validasi & input lama hanya berlaku untuk satu kali request.
$GLOBALS['_errors'] = $_SESSION['_errors'] ?? [];
$GLOBALS['_old'] = $_SESSION['_old'] ?? [];
unset($_SESSION['_errors'], $_SESSION['_old']);

$routes = require APP_PATH . '/routes.php';
$page = (string) ($_GET['page'] ?? (Auth::check() ? 'dashboard' : 'login'));
$action = (string) ($_GET['action'] ?? 'index');

if (!isset($routes[$page])) {
    abort(404, 'Halaman yang Anda cari tidak tersedia.');
}
[$controllerClass, $roles] = $routes[$page];

// Kontrol akses berbasis peran
if ($roles !== 'guest') {
    if (!Auth::check()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('login');
    }
    if (!in_array(Auth::role(), $roles, true)) {
        abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
    }
}

// Nama aksi: huruf kecil dan tanda hubung, diubah ke camelCase (simpan-nilai -> simpanNilai)
if (!preg_match('/^[a-z][a-z-]*$/', $action)) {
    abort(404);
}
$method = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $action))));
$controller = new $controllerClass();
if (!method_exists($controller, $method) || !(new ReflectionMethod($controller, $method))->isPublic()) {
    abort(404, 'Aksi tidak dikenal.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
} elseif (in_array($method, $controller->postActions, true)) {
    abort(405, 'Aksi ini hanya dapat dilakukan melalui formulir.');
}

try {
    $controller->$method();
} catch (PDOException $e) {
    error_log('[SIAKAD] ' . $e->getMessage());
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        flash('error', db_error_message($e));
        $back = $_SERVER['HTTP_REFERER'] ?? url($page);
        header('Location: ' . $back);
        exit;
    }
    abort(500, db_error_message($e));
}
