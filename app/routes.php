<?php
/**
 * Daftar halaman: 'page' => [Controller, peran yang diizinkan | 'guest'].
 */
return [
    'login'                => ['AuthController', 'guest'],
    'logout'               => ['AuthController', ['admin', 'dosen', 'mahasiswa']],
    'dashboard'            => ['DashboardController', ['admin', 'dosen', 'mahasiswa']],
    'profil'               => ['ProfilController', ['admin', 'dosen', 'mahasiswa']],

    // Administrator
    'admin/fakultas'       => ['FakultasController', ['admin']],
    'admin/prodi'          => ['ProdiController', ['admin']],
    'admin/dosen'          => ['DosenController', ['admin']],
    'admin/mahasiswa'      => ['MahasiswaController', ['admin']],
    'admin/mata-kuliah'    => ['MataKuliahController', ['admin']],
    'admin/tahun-akademik' => ['TahunAkademikController', ['admin']],
    'admin/ruangan'        => ['RuanganController', ['admin']],
    'admin/kelas'          => ['KelasController', ['admin']],
    'admin/pengguna'       => ['PenggunaController', ['admin']],
    'admin/sistem'         => ['SistemController', ['admin']],

    // Dosen
    'dosen/kelas'          => ['DosenKelasController', ['dosen']],
    'dosen/perwalian'      => ['PerwalianController', ['dosen']],

    // Mahasiswa
    'mahasiswa/krs'        => ['KrsController', ['mahasiswa']],
    'mahasiswa/khs'        => ['KhsController', ['mahasiswa']],
    'mahasiswa/transkrip'  => ['TranskripController', ['mahasiswa']],
];
