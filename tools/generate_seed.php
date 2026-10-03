<?php
/**
 * Pembuat data contoh (seed) untuk SIAKAD.
 * Menghasilkan database/seed.sql secara deterministik (mt_srand tetap),
 * sehingga hasilnya sama setiap kali dijalankan.
 *
 * Pemakaian:  php tools/generate_seed.php
 * Semua nama orang, NIDN, NPM, dan email di sini fiktif.
 */

mt_srand(2026);

$out = [];
$q = fn($v) => $v === null ? 'NULL' : "'" . str_replace("'", "''", (string) $v) . "'";
$row = fn(array $vals) => '(' . implode(', ', array_map($q, $vals)) . ')';

$out[] = "-- =====================================================================";
$out[] = "--  SIAKAD - data contoh (fiktif). Dibuat oleh tools/generate_seed.php";
$out[] = "--  Akun demo: admin / admin123, dosen (NIDN) / dosen123, mahasiswa (NPM) / mhs123";
$out[] = "-- =====================================================================";
$out[] = "USE db_siakad;";
$out[] = "SET FOREIGN_KEY_CHECKS = 0;";
foreach (['nilai', 'krs', 'kelas', 'skala_nilai', 'ruangan', 'tahun_akademik', 'mata_kuliah', 'mahasiswa', 'dosen', 'users', 'program_studi', 'fakultas'] as $t) {
    $out[] = "TRUNCATE TABLE $t;";
}
$out[] = "SET FOREIGN_KEY_CHECKS = 1;";
$out[] = "";

// ---------------------------------------------------------------- fakultas
$fakultas = [
    [1, 'FMIPA', 'Fakultas Matematika dan Ilmu Pengetahuan Alam'],
    [2, 'FT',    'Fakultas Teknik'],
];
$out[] = "INSERT INTO fakultas (id_fakultas, kode_fakultas, nama_fakultas) VALUES";
$out[] = implode(",\n", array_map($row, $fakultas)) . ";\n";

// ---------------------------------------------------------------- prodi
$prodi = [
    [1, 'INF', 'Informatika',     'S1', 1],
    [2, 'STA', 'Statistika',      'S1', 1],
    [3, 'MAT', 'Matematika',      'S1', 1],
    [4, 'TEL', 'Teknik Elektro',  'S1', 2],
    [5, 'TSP', 'Teknik Sipil',    'S1', 2],
];
$out[] = "INSERT INTO program_studi (id_prodi, kode_prodi, nama_prodi, jenjang, id_fakultas) VALUES";
$out[] = implode(",\n", array_map($row, $prodi)) . ";\n";

// ---------------------------------------------------------------- users + dosen
$hashAdmin = password_hash('admin123', PASSWORD_BCRYPT);
$hashDosen = password_hash('dosen123', PASSWORD_BCRYPT);
$hashMhs   = password_hash('mhs123', PASSWORD_BCRYPT);

$users = [[1, 'admin', $hashAdmin, 'admin', 1]];

$dosenNama = [
    // nama, prodi
    ['Dr. Rahmat Hidayat, S.Kom., M.Kom.', 1],
    ['Nurul Fadhilah, S.Si., M.Sc.',       1],
    ['Ir. Teuku Iskandar, M.T.',           1],
    ['Dr. Cut Meurah Intan, M.Si.',        2],
    ['Fajar Ramadhan, S.Stat., M.Stat.',   2],
    ['Dr. Zulfikar Amin, M.Kom.',          1],
    ['Siti Rahmah, S.Pd., M.Si.',          3],
];
$dosen = [];
$idUser = 2;
foreach ($dosenNama as $i => [$nama, $idProdi]) {
    $nidn = sprintf('00%02d%02d%04d', 10 + $i, 1 + $i, 8101 + $i * 7);
    $slug = strtolower(preg_replace('/[^a-z]+/i', '.', explode(',', preg_replace('/^(Dr\.|Ir\.)\s*/', '', $nama))[0]));
    $email = trim($slug, '.') . '@dosen.siakad.test';
    $hp = '0852' . sprintf('%08d', mt_rand(10000000, 99999999));
    $users[] = [$idUser, $nidn, $hashDosen, 'dosen', 1];
    $dosen[] = [$i + 1, $nidn, $nama, $email, $hp, $idProdi, $idUser];
    $idUser++;
}

// ---------------------------------------------------------------- mahasiswa
$namaDepanL = ['Muhammad', 'Rizki', 'Teuku', 'Fadhil', 'Aulia', 'Iqbal', 'Haikal', 'Rafi', 'Khairul', 'Zikri', 'Akbar', 'Farhan', 'Ilham', 'Dimas'];
$namaDepanP = ['Nadia', 'Cut', 'Putri', 'Syarifah', 'Rahmi', 'Intan', 'Aisyah', 'Nurul', 'Dara', 'Salsabila', 'Zahra', 'Fitri', 'Annisa', 'Mutia'];
$namaBelakang = ['Maulana', 'Saputra', 'Ramadhani', 'Azzahra', 'Hidayati', 'Fikri', 'Rahman', 'Amalia', 'Pratama', 'Kurniawan', 'Safitri', 'Husna', 'Firdaus', 'Akmal', 'Yusra', 'Nabila'];

$mahasiswa = [];
$idMhs = 1;
$usedNames = [];
$groups = [
    // prodi, angkatan, jumlah, kode npm, dosen wali (dipilih bergantian)
    [1, 2025, 16, '08107010', [2, 1]],
    [2, 2025, 6,  '08108010', [4]],
    [1, 2026, 6,  '08107010', [6]],
];
foreach ($groups as [$idProdi, $angkatan, $jumlah, $kodeNpm, $wali]) {
    for ($n = 1; $n <= $jumlah; $n++) {
        do {
            $jk = mt_rand(0, 1) ? 'L' : 'P';
            $depan = $jk === 'L' ? $namaDepanL[mt_rand(0, count($namaDepanL) - 1)] : $namaDepanP[mt_rand(0, count($namaDepanP) - 1)];
            $nama = $depan . ' ' . $namaBelakang[mt_rand(0, count($namaBelakang) - 1)];
        } while (isset($usedNames[$nama]));
        $usedNames[$nama] = true;
        $npm = substr((string) $angkatan, 2) . $kodeNpm . sprintf('%03d', $n);
        $lahir = sprintf('%d-%02d-%02d', $angkatan - 18 - mt_rand(0, 1), mt_rand(1, 12), mt_rand(1, 28));
        $email = $npm . '@mhs.siakad.test';
        $users[] = [$idUser, $npm, $hashMhs, 'mahasiswa', 1];
        $mahasiswa[] = [$idMhs, $npm, $nama, $jk, $lahir, $email, $angkatan, $idProdi, $wali[($n - 1) % count($wali)], $idUser];
        $idMhs++;
        $idUser++;
    }
}

$out[] = "INSERT INTO users (id_user, username, password_hash, role, is_aktif) VALUES";
$out[] = implode(",\n", array_map($row, $users)) . ";\n";
$out[] = "INSERT INTO dosen (id_dosen, nidn, nama_dosen, email, no_hp, id_prodi, id_user) VALUES";
$out[] = implode(",\n", array_map($row, $dosen)) . ";\n";
$out[] = "INSERT INTO mahasiswa (id_mahasiswa, npm, nama_mahasiswa, jenis_kelamin, tanggal_lahir, email, angkatan, id_prodi, id_dosen_wali, id_user) VALUES";
$out[] = implode(",\n", array_map($row, $mahasiswa)) . ";\n";

// ---------------------------------------------------------------- mata kuliah
// [id, kode, nama, sks, semester, jenis, prodi, dosen pengampu]
$mk = [
    [1,  'INF101', 'Algoritma dan Pemrograman',             4, 1, 'Wajib', 1, 1],
    [2,  'INF102', 'Kalkulus I',                            3, 1, 'Wajib', 1, 3],
    [3,  'INF103', 'Pengantar Teknologi Informasi',         2, 1, 'Wajib', 1, 6],
    [4,  'INF104', 'Logika Informatika',                    3, 1, 'Wajib', 1, 2],
    [5,  'INF201', 'Struktur Data',                         3, 2, 'Wajib', 1, 1],
    [6,  'INF202', 'Matematika Diskrit',                    3, 2, 'Wajib', 1, 3],
    [7,  'INF203', 'Basis Data',                            3, 2, 'Wajib', 1, 2],
    [8,  'INF204', 'Pemrograman Berorientasi Objek',        3, 2, 'Wajib', 1, 6],
    [9,  'INF301', 'Manajemen dan Pemodelan Data',          3, 3, 'Wajib', 1, 2],
    [10, 'INF302', 'Pemrograman Web',                       3, 3, 'Wajib', 1, 6],
    [11, 'INF303', 'Sistem Operasi',                        3, 3, 'Wajib', 1, 3],
    [12, 'INF304', 'Jaringan Komputer',                     3, 3, 'Wajib', 1, 1],
    [13, 'INF305', 'Rekayasa Perangkat Lunak',              3, 3, 'Wajib', 1, 6],
    [14, 'STA101', 'Pengantar Statistika',                  3, 1, 'Wajib', 2, 4],
    [15, 'STA102', 'Kalkulus Dasar',                        3, 1, 'Wajib', 2, 7],
    [16, 'STA103', 'Pengantar Komputasi Statistika',        2, 1, 'Wajib', 2, 5],
    [17, 'STA201', 'Teori Peluang',                         3, 2, 'Wajib', 2, 4],
    [18, 'STA202', 'Aljabar Linear',                        3, 2, 'Wajib', 2, 7],
    [19, 'STA203', 'Metode Statistika',                     3, 2, 'Wajib', 2, 5],
    [20, 'STA301', 'Analisis Regresi',                      3, 3, 'Wajib', 2, 4],
    [21, 'STA302', 'Statistika Matematika',                 3, 3, 'Wajib', 2, 5],
    [22, 'STA303', 'Basis Data untuk Statistika',           3, 3, 'Pilihan', 2, 2],
];
$out[] = "INSERT INTO mata_kuliah (id_mk, kode_mk, nama_mk, sks, semester_paket, jenis, id_prodi) VALUES";
$out[] = implode(",\n", array_map(fn($m) => $row(array_slice($m, 0, 7)), $mk)) . ";\n";

// ---------------------------------------------------------------- tahun akademik
$ta = [
    [1, '2025/2026', 'Ganjil', 0],
    [2, '2025/2026', 'Genap',  0],
    [3, '2026/2027', 'Ganjil', 1],
];
$out[] = "INSERT INTO tahun_akademik (id_ta, tahun, semester, is_aktif) VALUES";
$out[] = implode(",\n", array_map($row, $ta)) . ";\n";

// ---------------------------------------------------------------- ruangan
$ruangan = [
    [1, 'MIPA-101', 'Ruang Kuliah 101', 'Gedung FMIPA Lt. 1', 40],
    [2, 'MIPA-102', 'Ruang Kuliah 102', 'Gedung FMIPA Lt. 1', 40],
    [3, 'MIPA-201', 'Ruang Kuliah 201', 'Gedung FMIPA Lt. 2', 50],
    [4, 'LAB-KOM1', 'Laboratorium Komputer 1', 'Gedung Informatika', 30],
    [5, 'LAB-KOM2', 'Laboratorium Komputer 2', 'Gedung Informatika', 30],
    [6, 'FT-A301',  'Ruang Kuliah A301', 'Gedung Teknik A', 60],
];
$out[] = "INSERT INTO ruangan (id_ruangan, kode_ruangan, nama_ruangan, gedung, kapasitas) VALUES";
$out[] = implode(",\n", array_map($row, $ruangan)) . ";\n";

// ---------------------------------------------------------------- skala nilai
$skala = [
    ['A',  87, 101, 4.00],
    ['AB', 78, 87,  3.50],
    ['B',  69, 78,  3.00],
    ['BC', 60, 69,  2.50],
    ['C',  51, 60,  2.00],
    ['D',  41, 51,  1.00],
    ['E',  0,  41,  0.00],
];
$out[] = "INSERT INTO skala_nilai (huruf, batas_bawah, batas_atas, bobot) VALUES";
$out[] = implode(",\n", array_map($row, $skala)) . ";\n";

// ---------------------------------------------------------------- kelas
// Semester 1 dibuka di TA 1 (angkatan 2025) dan TA 3 (angkatan 2026),
// semester 2 di TA 2, semester 3 di TA 3.
$hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
$slot = [['08:00:00', '09:40:00'], ['10:00:00', '11:40:00'], ['13:30:00', '15:10:00'], ['15:30:00', '17:10:00']];
$kelas = [];
$idKelas = 1;
$kelasIndex = []; // [id_ta][id_mk] => id_kelas
$slotCounter = [];
foreach ($mk as [$id, $kode, $nama, $sks, $sem, $jenis, $idProdi, $idDosen]) {
    $taList = $sem === 1 ? [1, 3] : ($sem === 2 ? [2] : [3]);
    if ($idProdi === 2 && $sem === 1) {
        $taList = [1]; // angkatan 2026 prodi Statistika tidak ada di data contoh
    }
    foreach ($taList as $idTa) {
        $key = $idTa . '-' . $idProdi . '-' . $sem;
        $c = $slotCounter[$key] = ($slotCounter[$key] ?? -1) + 1;
        $h = $hari[$c % 5];
        [$mulai, $selesai] = $slot[($c + $idProdi) % 4];
        $ruang = $kode === 'INF302' || $kode === 'INF101' || $kode === 'INF204' ? 4 : ($idProdi === 2 ? 3 : 1 + ($c % 2));
        $kelas[] = [$idKelas, $id, $idTa, $idDosen, $ruang, '01', $h, $mulai, $selesai, 40];
        $kelasIndex[$idTa][$id] = $idKelas;
        $idKelas++;
    }
}
$out[] = "INSERT INTO kelas (id_kelas, id_mk, id_ta, id_dosen, id_ruangan, nama_kelas, hari, jam_mulai, jam_selesai, kuota) VALUES";
$out[] = implode(",\n", array_map($row, $kelas)) . ";\n";

// ---------------------------------------------------------------- KRS + nilai
$krs = [];
$nilai = [];
$idKrs = 1;
$clamp = fn($v) => max(0, min(100, $v));
$mkByProdiSem = [];
foreach ($mk as $m) {
    $mkByProdiSem[$m[6]][$m[4]][] = $m[0];
}

foreach ($mahasiswa as $m) {
    [$idM, $npm, , , , , $angkatan, $idProdi] = $m;
    $kemampuan = mt_rand(55, 92); // tingkat kemampuan dasar mahasiswa (fiktif)
    $plan = $angkatan == 2025 ? [1 => 1, 2 => 2, 3 => 3] : [3 => 1]; // id_ta => semester paket
    foreach ($plan as $idTa => $sem) {
        foreach ($mkByProdiSem[$idProdi][$sem] ?? [] as $idMk) {
            if (!isset($kelasIndex[$idTa][$idMk])) {
                continue;
            }
            $aktif = $idTa === 3;
            // Mahasiswa demo (NPM ...001 Informatika 2025) sengaja belum mengambil INF305
            // dan KRS-nya masih "Diajukan" agar alur KRS bisa diperagakan.
            $isDemo = $npm === '2508107010001';
            if ($isDemo && $idTa === 3 && $idMk === 13) {
                continue;
            }
            if ($angkatan == 2026) {
                // angkatan baru: sebagian sudah mengajukan KRS, sebagian belum
                if ($idM % 2 === 0) {
                    continue;
                }
            }
            $status = 'Disetujui';
            if ($aktif && ($isDemo || $angkatan == 2026)) {
                $status = 'Diajukan';
            }
            $tgl = $idTa === 1 ? '2025-08-25 09:00:00' : ($idTa === 2 ? '2026-02-09 09:00:00' : '2026-08-24 09:00:00');
            $krs[] = [$idKrs, $idM, $kelasIndex[$idTa][$idMk], $status, $tgl];

            if (!$aktif) {
                $tugas = $clamp($kemampuan + mt_rand(-5, 10));
                $uts   = $clamp($kemampuan + mt_rand(-15, 8));
                $uas   = $clamp($kemampuan + mt_rand(-12, 8));
                $nilai[] = [$idKrs, $tugas, $uts, $uas];
            } elseif ($status === 'Disetujui' && in_array($idMk, [9, 20], true)) {
                // semester berjalan: nilai tugas sudah masuk, UTS/UAS belum
                $nilai[] = [$idKrs, $clamp($kemampuan + mt_rand(-5, 8)), null, null];
            }
            $idKrs++;
        }
    }
}
$out[] = "INSERT INTO krs (id_krs, id_mahasiswa, id_kelas, status, tanggal_pengajuan) VALUES";
$out[] = implode(",\n", array_map($row, $krs)) . ";\n";
$out[] = "INSERT INTO nilai (id_krs, nilai_tugas, nilai_uts, nilai_uas) VALUES";
$out[] = implode(",\n", array_map($row, $nilai)) . ";\n";

file_put_contents(__DIR__ . '/../database/seed.sql', implode("\n", $out));
printf("seed.sql dibuat: %d users, %d dosen, %d mahasiswa, %d mata kuliah, %d kelas, %d KRS, %d nilai\n",
    count($users), count($dosen), count($mahasiswa), count($mk), count($kelas), count($krs), count($nilai));
