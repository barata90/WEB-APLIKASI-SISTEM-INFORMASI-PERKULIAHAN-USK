<?php
// Salin menjadi config.local.php, lalu isi sesuai server basis data yang dipakai.
// config.local.php tercantum di .gitignore sehingga password tidak ikut ter-upload.
return [
    'db_host' => '127.0.0.1',   // host MySQL/MariaDB, mis. 127.0.0.1 untuk Homebrew/XAMPP/MAMP
    'db_port' => 3306,          // MAMP memakai 8889
    'db_name' => 'db_siakad',
    'db_user' => 'root',
    'db_pass' => '',            // MAMP: 'root'
    'debug'   => true,
];
