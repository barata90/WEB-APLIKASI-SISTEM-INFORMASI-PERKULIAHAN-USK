<?php
/** Halaman informasi server: PHP, web server, basis data, tabel, dan foreign key. */
class SistemController extends Controller
{
    public function index(): void
    {
        $stat = new Statistik();
        $tabel = $stat->tabelDatabase();
        foreach ($tabel as &$t) {
            $t['jumlah_baris'] = $t['jenis'] === 'BASE TABLE' ? $stat->jumlahBaris($t['nama']) : null;
        }
        unset($t);

        $this->render('admin/sistem/index', [
            'title' => 'Informasi Sistem',
            'info'  => [
                'Web server'        => $_SERVER['SERVER_SOFTWARE'] ?? php_sapi_name(),
                'Versi PHP'         => PHP_VERSION . ' (' . php_sapi_name() . ')',
                'Sistem operasi'    => php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('m'),
                'Nama host server'  => $_SERVER['SERVER_NAME'] ?? '-',
                'Alamat server'     => ($_SERVER['SERVER_ADDR'] ?? '-') . ':' . ($_SERVER['SERVER_PORT'] ?? '-'),
                'Document root'     => $_SERVER['DOCUMENT_ROOT'] ?? '-',
                'Lokasi aplikasi'   => BASE_PATH,
                'RDBMS'             => $stat->versiDatabase(),
                'Nama basis data'   => config('db_name'),
                'Ekstensi PDO'      => implode(', ', PDO::getAvailableDrivers()),
                'Zona waktu'        => date_default_timezone_get() . ' (' . date('d-m-Y H:i:s') . ')',
            ],
            'tabel' => $tabel,
            'fk'    => $stat->foreignKeys(),
        ]);
    }
}
