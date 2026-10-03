<?php
/**
 * Konfigurasi utama aplikasi.
 * Urutan prioritas: nilai bawaan < variabel lingkungan (DB_HOST, DB_PORT,
 * DB_NAME, DB_USER, DB_PASS, APP_DEBUG) < config/config.local.php.
 * Nilai bawaan cocok untuk XAMPP/MAMP (user root tanpa password).
 * File config.local.php tidak ikut di-commit, jadi kredensial tidak masuk ke GitHub.
 */
$env = static function (string $key, $default) {
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
};

$config = [
    'app_name'     => 'SIAKAD',
    'app_subtitle' => 'Sistem Informasi Perkuliahan',
    'db_host'      => $env('DB_HOST', '127.0.0.1'),
    'db_port'      => (int) $env('DB_PORT', 3306),
    'db_name'      => $env('DB_NAME', 'db_siakad'),
    'db_user'      => $env('DB_USER', 'root'),
    'db_pass'      => $env('DB_PASS', ''),
    'timezone'     => 'Asia/Jakarta',
    'debug'        => filter_var($env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_merge($config, require $local);
}

return $config;
