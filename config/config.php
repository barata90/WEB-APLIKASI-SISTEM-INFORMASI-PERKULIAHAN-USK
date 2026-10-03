<?php
/**
 * Konfigurasi utama aplikasi.
 * Nilai bawaan cocok untuk XAMPP/Laragon (user root tanpa password).
 * Untuk lingkungan lain, salin config.local.example.php menjadi
 * config.local.php lalu ubah isinya. File config.local.php tidak ikut di-commit.
 */
$config = [
    'app_name'     => 'SIAKAD',
    'app_subtitle' => 'Sistem Informasi Perkuliahan',
    'db_host'      => '127.0.0.1',
    'db_port'      => 3306,
    'db_name'      => 'db_siakad',
    'db_user'      => 'root',
    'db_pass'      => '',
    'timezone'     => 'Asia/Jakarta',
    'debug'        => false,
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_merge($config, require $local);
}

return $config;
