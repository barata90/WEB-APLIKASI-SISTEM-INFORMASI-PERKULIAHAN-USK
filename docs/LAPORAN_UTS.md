# LAPORAN UTS
## WEB APLIKASI SISTEM INFORMASI PERKULIAHAN (SIAKAD)

| | |
|---|---|
| Mata kuliah | Manajemen dan Pemodelan Data |
| Nama | ............................................ |
| NPM | ............................................ |
| Program studi | ............................................ |
| Tanggal | 3 Oktober 2026 |
| Repositori kode | <https://github.com/barata90/WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK> |

**Ringkasan.** Laporan ini mendokumentasikan pembangunan SIAKAD, sebuah aplikasi web sistem informasi perkuliahan dengan tiga peran pengguna: administrator, dosen, dan mahasiswa. Aplikasi ditulis dengan PHP 8.3 tanpa framework (pola MVC sederhana dan PDO), dijalankan di Apache HTTP Server 2.4.58, dan menyimpan data di MariaDB 10.11 yang kompatibel dengan MySQL. Basis data `db_siakad` terdiri atas 12 tabel dan 3 view, dirancang hingga bentuk normal ketiga (3NF), dan menegakkan integritas data melalui *primary key*, *foreign key*, `UNIQUE`, dan `CHECK`. Seluruh screenshot pada laporan diambil dari aplikasi yang benar-benar berjalan di `http://localhost/siakad/`, sedangkan keluaran terminal berasal dari perintah yang dijalankan di server yang sama. Data mahasiswa, dosen, dan nilai di dalamnya adalah data contoh fiktif.

---

## 1. Laporan Komputer Server dan Web Server

### 1.1 Spesifikasi perangkat keras

Aplikasi dibangun dan diuji pada sebuah server virtual (VM) Linux. Spesifikasi berikut diambil langsung dari perintah `lscpu`, `free -h`, dan `df -h` (Gambar 1.1).

| Komponen | Spesifikasi |
|---|---|
| Prosesor | Intel(R) Xeon(R) Processor @ 2.80GHz, arsitektur x86_64 |
| Jumlah CPU | 4 vCPU (1 socket, 4 core per socket, 1 thread per core), L3 cache 33 MiB |
| Memori (RAM) | 15 GiB |
| Penyimpanan | Disk virtual `/dev/vda` 252 GB, sistem berkas root `/` |
| Jaringan | Antarmuka loopback `127.0.0.1` (akses localhost) |

![Gambar 1.1 Spesifikasi perangkat keras server](screenshots/terminal/T01-spesifikasi-hardware.png)

### 1.2 Spesifikasi perangkat lunak

| Perangkat lunak | Versi | Fungsi |
|---|---|---|
| Sistem operasi | Ubuntu 24.04.4 LTS (kernel Linux 6.18, x86_64) | Sistem operasi server |
| Web server | Apache HTTP Server 2.4.58 (Ubuntu) | Melayani permintaan HTTP di port 80 |
| Bahasa pemrograman | PHP 8.3.6 (modul `mod_php`/apache2handler) | Mengeksekusi logika aplikasi |
| RDBMS | MariaDB 10.11.14 | Menyimpan data di port 3306 |
| Ekstensi PHP | PDO, pdo_mysql, mbstring, session | Koneksi basis data dan pengelolaan sesi |
| Peramban uji | Chromium (Playwright) | Akses klien dan pengambilan screenshot |
| Pendukung dokumentasi | Graphviz, Pandoc | Pembuatan diagram dan laporan |

![Gambar 1.2 Versi sistem operasi, Apache, PHP, MariaDB, dan modul Apache yang aktif](screenshots/terminal/T02-spesifikasi-software.png)

### 1.3 Konfigurasi web server Apache

Apache dipasang dari repositori Ubuntu bersama `libapache2-mod-php8.3`, sehingga berkas `.php` dieksekusi langsung oleh modul PHP di dalam proses Apache. Folder proyek ditautkan (symlink) ke `/var/www/html/siakad`, lalu dibuat berkas konfigurasi `/etc/apache2/conf-available/siakad.conf` yang berisi:

```apache
# SIAKAD - dapat diakses di http://localhost/siakad/
ServerName localhost
Alias /siakad /var/www/html/siakad
<Directory /var/www/html/siakad>
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

`ServerName localhost` menghilangkan peringatan AH00558, `Alias /siakad` memetakan URL ke folder proyek, dan `AllowOverride All` mengizinkan berkas `.htaccess` proyek berlaku. Konfigurasi diaktifkan dengan perintah berikut:

```bash
sudo ln -sfn /home/user/WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK /var/www/html/siakad
sudo a2enmod rewrite          # aktifkan mod_rewrite untuk .htaccess
sudo a2enconf siakad          # aktifkan konfigurasi siakad.conf
sudo apache2ctl configtest    # hasil: Syntax OK
sudo service apache2 reload
```

Berkas `.htaccess` di akar proyek menonaktifkan *directory listing*, menjadikan `index.php` sebagai halaman bawaan, dan menolak akses langsung ke folder `app/`, `config/`, `database/`, `tools/`, `docs/` serta berkas `.sql`, `.md`, `.log`. Pengujian dengan `curl` menunjukkan `config/config.php` dan `database/schema.sql` mengembalikan HTTP 403, sedangkan halaman aplikasi mengembalikan HTTP 200 (Gambar 1.5).

![Gambar 1.3 Konfigurasi Apache, symlink folder proyek, dan hasil configtest](screenshots/terminal/T03-konfigurasi-apache.png)

![Gambar 1.4 Isi berkas .htaccess aplikasi](screenshots/terminal/T04-htaccess.png)

### 1.4 Koneksi antara browser dan server

Pengguna membuka `http://localhost/siakad/` di browser. Browser mengirim permintaan HTTP (GET untuk membuka halaman, POST untuk mengirim formulir) ke Apache di port 80. Apache meneruskan berkas `index.php` ke modul PHP. Skrip PHP membaca sesi login, menentukan controller sesuai parameter `page` dan `action`, lalu menjalankan query SQL ke MariaDB melalui PDO di port 3306. Hasil query dirender menjadi HTML dan dikirim kembali ke browser sebagai respons HTTP `200 OK` dengan `Content-Type: text/html; charset=UTF-8`. Sesi pengguna disimpan dalam cookie `SIAKADSESSID` yang diberi atribut `HttpOnly` dan `SameSite=Lax`. Status layanan dan port yang terbuka ditunjukkan pada Gambar 1.6: Apache mendengarkan di `0.0.0.0:80`, sedangkan MariaDB hanya mendengarkan di `127.0.0.1:3306` sehingga basis data tidak dapat diakses langsung dari luar server.

![Gambar 1.5 Uji koneksi HTTP: halaman aplikasi 200 OK, folder konfigurasi dan SQL 403 Forbidden](screenshots/terminal/T06-uji-http.png)

![Gambar 1.6 Status layanan Apache dan MariaDB serta port yang terbuka](screenshots/terminal/T05-layanan-port.png)

### 1.5 Diagram alur komunikasi client-server

![Gambar 1.7 Diagram alur komunikasi client-server SIAKAD](diagram/01-arsitektur-client-server.png)

Urutan komunikasi pada Gambar 1.7 adalah: (1) browser mengirim HTTP request, (2) Apache mengeksekusi skrip PHP, (3) PHP mengirim query SQL lewat PDO dengan *prepared statement*, (4) MariaDB mengembalikan *result set*, (5) PHP merender HTML, dan (6) Apache mengirim HTTP response ke browser.

---

## 2. Laporan Bahasa Pemrograman Web (PHP)

### 2.1 Peran PHP dalam aplikasi

PHP berperan sebagai bahasa sisi server (*server-side scripting*). Seluruh logika bisnis dijalankan di server sehingga aturan seperti batas SKS, kuota kelas, dan hak akses tidak dapat dilewati dari sisi browser. Aplikasi ini tidak memakai framework agar setiap bagian (routing, koneksi basis data, validasi, autentikasi) terlihat jelas, tetapi tetap disusun mengikuti pola MVC (*Model-View-Controller*):

1. **Front controller** (`index.php`) menerima semua permintaan, memulai sesi, memuat kelas secara otomatis (`spl_autoload_register`), memeriksa hak akses berdasarkan peran, memverifikasi token CSRF untuk setiap POST, lalu memanggil method controller yang sesuai.
2. **Controller** (`app/controllers/`) memvalidasi input, memanggil model, dan memilih view.
3. **Model** (`app/models/`) berisi seluruh query SQL dengan PDO *prepared statement*, termasuk transaksi (`beginTransaction`, `commit`, `rollBack`).
4. **View** (`app/views/`) adalah template PHP yang menampilkan data; semua keluaran di-*escape* dengan `htmlspecialchars` melalui fungsi `e()` untuk mencegah XSS.

Aspek keamanan yang diterapkan di PHP: password disimpan sebagai hash bcrypt (`password_hash`/`password_verify`), `session_regenerate_id(true)` setelah login untuk mencegah *session fixation*, token CSRF pada setiap formulir, query berparameter untuk mencegah *SQL injection*, serta pemeriksaan kepemilikan data (dosen hanya dapat mengisi nilai kelas yang diampunya dan hanya dapat menyetujui KRS mahasiswa bimbingannya).

### 2.2 Koneksi ke basis data

Koneksi dibuat satu kali per permintaan (pola *singleton*) di `app/core/Database.php`:

```php
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
               $c['db_host'], $c['db_port'], $c['db_name']);
self::$pdo = new PDO($dsn, $c['db_user'], $c['db_pass'], [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // galat menjadi exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,                   // prepared statement asli
]);
```

Parameter koneksi disimpan di `config/config.php` (bawaan XAMPP: user `root` tanpa password) dan dapat ditimpa oleh `config/config.local.php` yang tidak ikut di-commit. Di server uji dipakai user khusus `siakad_app` yang hanya diberi hak `SELECT, INSERT, UPDATE, DELETE` pada `db_siakad`.

### 2.3 Contoh potongan kode CRUD utama

Contoh berikut diambil dari modul data mahasiswa (`app/models/Mahasiswa.php` dan `app/controllers/MahasiswaController.php`). Modul lain (fakultas, prodi, dosen, mata kuliah, ruangan, tahun akademik, kelas) mengikuti pola yang sama.

**Create.** Penambahan mahasiswa sekaligus membuat akun login (username = NPM). Kedua `INSERT` dibungkus dalam satu transaksi sehingga tidak ada akun tanpa mahasiswa atau sebaliknya.

```php
public function create(array $d, string $password): int
{
    $this->db->beginTransaction();
    try {
        $this->execute(
            "INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'mahasiswa')",
            [$d['npm'], password_hash($password, PASSWORD_BCRYPT)]
        );
        $idUser = (int) $this->db->lastInsertId();

        $this->execute(
            'INSERT INTO mahasiswa
                (npm, nama_mahasiswa, jenis_kelamin, tanggal_lahir, email, angkatan, id_prodi, id_dosen_wali, id_user)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$d['npm'], $d['nama_mahasiswa'], $d['jenis_kelamin'], nullable($d['tanggal_lahir']),
             nullable($d['email']), $d['angkatan'], $d['id_prodi'], nullable($d['id_dosen_wali']), $idUser]
        );
        $id = (int) $this->db->lastInsertId();
        $this->db->commit();
        return $id;
    } catch (Throwable $e) {
        $this->db->rollBack();
        throw $e;
    }
}
```

**Read.** Daftar mahasiswa dengan `JOIN` ke prodi, dosen wali, akun, dan view IPK, serta filter pencarian yang tetap memakai parameter.

```php
public function all(string $cari = '', ?int $idProdi = null, ?int $angkatan = null): array
{
    $sql = "SELECT m.id_mahasiswa, m.npm, m.nama_mahasiswa, m.jenis_kelamin, m.angkatan, m.email,
                   p.nama_prodi, p.jenjang, d.nama_dosen AS dosen_wali, u.is_aktif,
                   ipk.ipk, ipk.total_sks
            FROM mahasiswa m
            JOIN program_studi p ON p.id_prodi = m.id_prodi
            LEFT JOIN dosen d    ON d.id_dosen = m.id_dosen_wali
            LEFT JOIN users u    ON u.id_user  = m.id_user
            LEFT JOIN v_ipk ipk  ON ipk.id_mahasiswa = m.id_mahasiswa
            WHERE 1 = 1";
    $params = [];
    if ($cari !== '') {
        $sql .= ' AND (m.npm LIKE ? OR m.nama_mahasiswa LIKE ?)';
        $params[] = "%$cari%";
        $params[] = "%$cari%";
    }
    if ($idProdi)  { $sql .= ' AND m.id_prodi = ?'; $params[] = $idProdi; }
    if ($angkatan) { $sql .= ' AND m.angkatan = ?'; $params[] = $angkatan; }
    return $this->fetchAll($sql . ' ORDER BY m.angkatan DESC, m.npm', $params);
}
```

**Update.** Biodata diperbarui, lalu username akun disinkronkan dengan NPM menggunakan `UPDATE ... JOIN`.

```php
$n = $this->execute(
    'UPDATE mahasiswa
     SET npm = ?, nama_mahasiswa = ?, jenis_kelamin = ?, tanggal_lahir = ?, email = ?,
         angkatan = ?, id_prodi = ?, id_dosen_wali = ?
     WHERE id_mahasiswa = ?',
    [$d['npm'], $d['nama_mahasiswa'], $d['jenis_kelamin'], nullable($d['tanggal_lahir']),
     nullable($d['email']), $d['angkatan'], $d['id_prodi'], nullable($d['id_dosen_wali']), $id]
);
$this->execute(
    'UPDATE users u JOIN mahasiswa m ON m.id_user = u.id_user
     SET u.username = m.npm WHERE m.id_mahasiswa = ?',
    [$id]
);
```

**Delete.** Penghapusan dilakukan lewat formulir POST yang dilindungi token CSRF. Jika mahasiswa sudah memiliki KRS, MariaDB menolak penghapusan (galat 1451, `ON DELETE RESTRICT`) dan aplikasi menampilkan pesan yang dapat dipahami pengguna.

```php
// Model
$idUser = $this->scalar('SELECT id_user FROM mahasiswa WHERE id_mahasiswa = ?', [$id]);
$n = $this->execute('DELETE FROM mahasiswa WHERE id_mahasiswa = ?', [$id]);
if ($idUser) {
    $this->execute('DELETE FROM users WHERE id_user = ?', [$idUser]);
}

// Controller: validasi sebelum CREATE
$rules = [
    'npm'            => 'required|digits:13',
    'nama_mahasiswa' => 'required|max:100',
    'jenis_kelamin'  => 'required|in:L,P',
    'email'          => 'email|max:100',
    'angkatan'       => 'required|int|min:2000|max:2100',
    'id_prodi'       => 'required|int',
];
if ($errors = $this->validate($_POST, $rules, $this->labels)) {
    $this->backWithErrors($errors, 'admin/mahasiswa', ['action' => 'create']);
}
$this->model->create($_POST, $_POST['password']);
```

Pemetaan galat basis data menjadi pesan pengguna ada di `app/core/helpers.php`:

```php
function db_error_message(PDOException $e): string
{
    return match ((int) ($e->errorInfo[1] ?? 0)) {
        1062 => 'Data dengan kode/nomor yang sama sudah ada (melanggar UNIQUE).',
        1451 => 'Data tidak dapat dihapus karena masih dipakai oleh data lain (foreign key).',
        1452 => 'Data rujukan tidak ditemukan (foreign key tidak valid).',
        3819, 4025 => 'Nilai melanggar aturan CHECK pada basis data.',
        default => config('debug') ? $e->getMessage() : 'Terjadi kesalahan basis data.',
    };
}
```

### 2.4 Struktur folder dan file utama

![Gambar 2.1 Struktur folder proyek (keluaran perintah tree)](screenshots/terminal/T07-struktur-folder.png)

| Lokasi | Isi dan fungsi |
|---|---|
| `index.php` | Front controller: sesi, autoload, routing, kontrol akses, CSRF, penanganan galat |
| `.htaccess` | Aturan Apache: blokir folder internal dan berkas sensitif |
| `config/config.php` | Konfigurasi aplikasi dan koneksi basis data |
| `app/routes.php` | Daftar halaman beserta controller dan peran yang diizinkan |
| `app/core/` | `Database` (PDO), `Model` (helper query), `Controller` (render dan validasi), `Auth` (login/sesi), `helpers.php` |
| `app/models/` | Satu kelas per entitas: `Mahasiswa`, `Dosen`, `MataKuliah`, `Kelas`, `Krs`, `Nilai`, dan lainnya |
| `app/controllers/` | 18 controller untuk modul admin, dosen, dan mahasiswa |
| `app/views/` | Template HTML per modul, layout utama, halaman login, halaman galat |
| `assets/` | `css/style.css`, `js/app.js` (hitung nilai akhir langsung saat input), `img/logo.svg` |
| `database/schema.sql` | DDL lengkap: 12 tabel, constraint, dan 3 view |
| `database/seed.sql` | Data contoh fiktif |
| `tools/` | Pembuat data contoh, skrip screenshot otomatis, skrip pengambil keluaran terminal |
| `docs/` | Laporan, screenshot, diagram |

Kode aplikasi (PHP, CSS, JS) berjumlah sekitar 4.400 baris dan skema SQL sekitar 310 baris.

---

## 3. Laporan Database Server (RDBMS)

### 3.1 Jenis sistem basis data

RDBMS yang dipakai adalah **MariaDB 10.11.14**, turunan MySQL yang kompatibel pada level protokol dan sintaks SQL, sehingga aplikasi dapat dijalankan tanpa perubahan di MySQL 8 (misalnya MySQL bawaan XAMPP/Laragon). Semua tabel memakai *storage engine* **InnoDB** karena mendukung transaksi ACID dan *foreign key*, serta *character set* `utf8mb4` agar nama dengan karakter khusus tersimpan dengan benar. Server MariaDB hanya mendengarkan koneksi dari `127.0.0.1:3306`.

### 3.2 Struktur tabel utama

Basis data `db_siakad` berisi 12 tabel dan 3 view (Gambar 3.1 dan 3.2).

![Gambar 3.1 Daftar tabel dan view di db_siakad](screenshots/terminal/T08-daftar-tabel.png)

![Gambar 3.2 Halaman Informasi Sistem: jumlah baris per tabel dan daftar foreign key yang dibaca dari information_schema](screenshots/24-admin-informasi-sistem.png)

| No | Tabel | Keterangan | Primary key | Foreign key |
|---|---|---|---|---|
| 1 | `fakultas` | Data fakultas | `id_fakultas` | |
| 2 | `program_studi` | Program studi pada fakultas | `id_prodi` | `id_fakultas` → fakultas |
| 3 | `users` | Akun login dan peran | `id_user` | |
| 4 | `dosen` | Data dosen dan homebase | `id_dosen` | `id_prodi` → program_studi, `id_user` → users |
| 5 | `mahasiswa` | Biodata mahasiswa | `id_mahasiswa` | `id_prodi` → program_studi, `id_dosen_wali` → dosen, `id_user` → users |
| 6 | `mata_kuliah` | Kurikulum per prodi | `id_mk` | `id_prodi` → program_studi |
| 7 | `tahun_akademik` | Tahun dan semester akademik | `id_ta` | |
| 8 | `ruangan` | Ruang kuliah | `id_ruangan` | |
| 9 | `kelas` | Penawaran mata kuliah per semester | `id_kelas` | `id_mk`, `id_ta`, `id_dosen`, `id_ruangan` |
| 10 | `krs` | Mahasiswa mengambil kelas | `id_krs` | `id_mahasiswa` → mahasiswa, `id_kelas` → kelas |
| 11 | `nilai` | Komponen nilai per KRS | `id_nilai` | `id_krs` → krs |
| 12 | `skala_nilai` | Konversi angka ke huruf mutu | `huruf` | |

| View | Isi |
|---|---|
| `v_nilai_akhir` | Menghitung nilai akhir = 30% tugas + 30% UTS + 40% UAS, lalu menggabungkan dengan `skala_nilai` untuk mendapatkan huruf mutu dan bobot |
| `v_ips` | Indeks Prestasi Semester per mahasiswa per tahun akademik |
| `v_ipk` | Indeks Prestasi Kumulatif; jika mata kuliah diulang hanya bobot terbaik yang dihitung |

### 3.3 Tipe data

![Gambar 3.3 Struktur tabel mahasiswa (DESCRIBE)](screenshots/terminal/T09-describe-mahasiswa.png)

![Gambar 3.4 Struktur tabel krs dan nilai (DESCRIBE)](screenshots/terminal/T10-describe-krs-nilai.png)

Pemilihan tipe data mengikuti sifat datanya:

| Tipe data | Dipakai pada | Alasan |
|---|---|---|
| `INT UNSIGNED AUTO_INCREMENT` | Semua kolom `id_*` | *Surrogate key* yang stabil walaupun NPM/NIDN berubah; tidak perlu nilai negatif |
| `CHAR(13)`, `CHAR(10)` | `npm`, `nidn` | Panjang selalu tetap (13 dan 10 digit); disimpan sebagai teks agar nol di depan tidak hilang |
| `VARCHAR(n)` | Nama, email, kode | Panjang bervariasi |
| `ENUM(...)` | `role`, `jenis_kelamin`, `jenjang`, `semester`, `hari`, `status` | Domain nilai tertutup dan sedikit |
| `TINYINT UNSIGNED` + `CHECK` | `sks` (1–6), `semester_paket` (1–8) | Angka kecil dengan rentang yang ditegakkan basis data |
| `DECIMAL(5,2)` + `CHECK` | `nilai_tugas`, `nilai_uts`, `nilai_uas` (0–100) | Nilai pecahan tepat tanpa galat pembulatan *floating point* |
| `DECIMAL(3,2)` | `bobot` | Bobot mutu 0,00–4,00 |
| `DATE`, `YEAR`, `TIME`, `DATETIME`, `TIMESTAMP` | `tanggal_lahir`, `angkatan`, `jam_mulai`, `last_login`, `created_at` | Tipe temporal agar dapat dibandingkan dan dihitung |
| `TINYINT(1)` | `is_aktif` | Nilai boolean |

### 3.4 Hubungan antar tabel (primary key dan foreign key)

Setiap tabel memiliki satu *primary key* surrogate. Kunci alami tetap dijaga keunikannya dengan `UNIQUE` (misalnya `npm`, `nidn`, `kode_mk`, `username`), dan beberapa tabel memiliki kunci kandidat komposit: `kelas (id_mk, id_ta, nama_kelas)`, `krs (id_mahasiswa, id_kelas)`, `tahun_akademik (tahun, semester)`.

| Relasi | Kardinalitas | Aturan referensial |
|---|---|---|
| fakultas → program_studi | 1 : N | `ON DELETE RESTRICT` |
| program_studi → dosen, mahasiswa, mata_kuliah | 1 : N | `ON DELETE RESTRICT` |
| users → dosen / mahasiswa | 1 : 0..1 (`id_user` UNIQUE) | `ON DELETE SET NULL` |
| dosen (wali) → mahasiswa | 1 : N, opsional | `ON DELETE SET NULL` |
| mata_kuliah, tahun_akademik, dosen → kelas | 1 : N | `ON DELETE RESTRICT` |
| ruangan → kelas | 1 : N, opsional | `ON DELETE SET NULL` |
| mahasiswa ↔ kelas melalui krs | M : N | `ON DELETE RESTRICT` |
| krs → nilai | 1 : 0..1 (`id_krs` UNIQUE) | `ON DELETE CASCADE` |

`RESTRICT` dipakai pada data akademik agar riwayat nilai tidak ikut terhapus. `SET NULL` dipakai untuk relasi opsional (dosen wali, ruangan, akun). `CASCADE` hanya dipakai pada `nilai` karena nilai tidak bermakna tanpa KRS-nya. Semua `ON UPDATE CASCADE`. Uji constraint langsung di MariaDB ditunjukkan pada Gambar 3.5: kode mata kuliah ganda ditolak (galat 1062), penghapusan mata kuliah yang masih dipakai kelas ditolak (1451), SKS 9 ditolak oleh `CHECK` (4025), dan KRS untuk mahasiswa yang tidak ada ditolak (1452).

![Gambar 3.5 Uji integritas UNIQUE, FOREIGN KEY, dan CHECK di MariaDB](screenshots/terminal/T15-uji-constraint.png)

---

## 4. Laporan Relational Database, SQL, dan Normalisasi

### 4.1 Diagram relasi antar tabel (ERD)

ERD berikut memakai notasi *crow's foot*: garis tegak berarti tepat satu, lingkaran berarti opsional (nol), dan cabang tiga berarti banyak.

![Gambar 4.1 Entity Relationship Diagram basis data db_siakad](diagram/02-erd.png)

Entitas inti adalah `mahasiswa`, `dosen`, `mata_kuliah`, dan `kelas`. Relasi banyak-ke-banyak antara mahasiswa dan kelas diselesaikan oleh tabel asosiatif `krs`, sedangkan `kelas` sendiri merupakan entitas yang menghubungkan mata kuliah, tahun akademik, dosen pengampu, dan ruangan. Nilai disimpan di tabel `nilai` yang berelasi 1 : 0..1 dengan `krs`, sehingga KRS yang belum dinilai tidak memerlukan baris kosong.

### 4.2 Proses normalisasi hingga 3NF

Titik awal normalisasi adalah dokumen Kartu Hasil Studi (KHS) manual yang memuat identitas mahasiswa sekaligus daftar mata kuliah dan nilainya. Contoh datanya diambil dari basis data aplikasi (Gambar 4.3).

**a. Bentuk tidak normal (UNF).** Satu baris mewakili satu mahasiswa per semester, dan atribut mata kuliah muncul berulang (*repeating group*):

| NPM | Nama | Prodi | Fakultas | Dosen wali | Tahun/Semester | {Kode MK, Nama MK, SKS, Kelas, Pengampu, NIDN, Tugas, UTS, UAS, NA, Huruf, Bobot} |
|---|---|---|---|---|---|---|
| 2508107010001 | Rahmi Azzahra | Informatika | FMIPA | Nurul Fadhilah | 2025/2026 Ganjil | {INF101, Algoritma dan Pemrograman, 4, 01, Dr. Rahmat Hidayat, ...}, {INF102, Kalkulus I, 3, 01, Ir. Teuku Iskandar, ...}, {INF103, ...}, {INF104, ...} |

Masalahnya: jumlah mata kuliah berbeda tiap mahasiswa sehingga tidak dapat dibuat kolom tetap, dan pencarian per mata kuliah menjadi sulit.

**b. Bentuk normal pertama (1NF).** Kelompok berulang dipecah menjadi baris sehingga setiap sel bernilai atomik. Relasi 1NF yang terbentuk adalah:

> KHS_1NF (**npm**, nama, jenis_kelamin, angkatan, kode_prodi, nama_prodi, jenjang, kode_fakultas, nama_fakultas, nidn_wali, nama_wali, **tahun**, **semester**, **kode_mk**, nama_mk, sks, semester_paket, nama_kelas, hari, jam, kode_ruangan, nidn, nama_dosen, tugas, uts, uas, nilai_akhir, huruf, bobot)

dengan kunci gabungan `{npm, kode_mk, tahun, semester}`. Cuplikan datanya (sebagian kolom):

| npm | nama | nama_prodi | fakultas | tahun/smt | kode_mk | nama_mk | sks | nama_dosen | na | huruf |
|---|---|---|---|---|---|---|---|---|---|---|
| 2508107010001 | Rahmi Azzahra | Informatika | FMIPA | 2025/2026 Ganjil | INF101 | Algoritma dan Pemrograman | 4 | Dr. Rahmat Hidayat | 72,40 | B |
| 2508107010001 | Rahmi Azzahra | Informatika | FMIPA | 2025/2026 Ganjil | INF102 | Kalkulus I | 3 | Ir. Teuku Iskandar | 68,30 | BC |
| 2508107010002 | Nadia Rahman | Informatika | FMIPA | 2025/2026 Ganjil | INF101 | Algoritma dan Pemrograman | 4 | Dr. Rahmat Hidayat | 81,30 | AB |

Tabel 1NF masih menyimpan data berulang: nama mahasiswa, nama prodi, dan nama mata kuliah tertulis di setiap baris. Akibatnya muncul anomali. Anomali *update*: mengganti nama mata kuliah harus dilakukan di banyak baris. Anomali *insert*: mata kuliah baru tidak dapat dicatat sebelum ada mahasiswa yang mengambilnya. Anomali *delete*: menghapus satu-satunya mahasiswa yang mengambil sebuah mata kuliah juga menghapus informasi mata kuliah itu.

**c. Dependensi fungsional.** Gambar 4.2 merangkum dependensi fungsional yang teridentifikasi.

![Gambar 4.2 Dependensi fungsional: parsial (merah), penuh (hijau), transitif (oranye)](diagram/04-dependensi-fungsional.png)

- Dependensi parsial terhadap sebagian kunci: `npm → nama, angkatan, kode_prodi, nidn_wali`; `kode_mk → nama_mk, sks, semester_paket`; `{kode_mk, tahun, semester, nama_kelas} → nidn, hari, jam, ruang`.
- Dependensi penuh terhadap seluruh kunci: `{npm, kode_mk, tahun, semester} → tugas, uts, uas`.
- Dependensi transitif: `npm → kode_prodi → nama_prodi → kode_fakultas → nama_fakultas`; `kelas → nidn → nama_dosen`; `nilai_akhir → huruf → bobot`.
- Atribut turunan: `nilai_akhir` dihitung dari `tugas`, `uts`, `uas`.

**d. Bentuk normal kedua (2NF).** Atribut yang bergantung parsial dipindah ke tabel dengan kuncinya sendiri:

| Tabel 2NF | Kunci | Atribut |
|---|---|---|
| MAHASISWA | npm | nama, jenis_kelamin, angkatan, kode_prodi, nama_prodi, kode_fakultas, nama_fakultas, nidn_wali, nama_wali |
| MATA_KULIAH | kode_mk | nama_mk, sks, semester_paket, jenis, kode_prodi |
| KELAS | {kode_mk, tahun, semester, nama_kelas} | nidn, nama_dosen, hari, jam_mulai, jam_selesai, kode_ruangan, nama_ruangan, kuota |
| KRS_NILAI | {npm, kode_mk, tahun, semester} | nama_kelas, tugas, uts, uas, nilai_akhir, huruf, bobot |

Setiap atribut non-kunci kini bergantung penuh pada kunci tabelnya, tetapi masih ada dependensi transitif (misalnya `nama_prodi` di MAHASISWA bergantung pada `kode_prodi`, bukan langsung pada `npm`).

**e. Bentuk normal ketiga (3NF).** Dependensi transitif dihapus dengan memecah tabel menurut determinannya:

| Dependensi transitif | Penyelesaian di skema akhir |
|---|---|
| `kode_prodi → nama_prodi, jenjang, kode_fakultas` | Tabel `program_studi`; mahasiswa, dosen, dan mata kuliah hanya menyimpan `id_prodi` |
| `kode_fakultas → nama_fakultas` | Tabel `fakultas`; program studi menyimpan `id_fakultas` |
| `nidn → nama_dosen, email, no_hp` | Tabel `dosen`; kelas menyimpan `id_dosen`, mahasiswa menyimpan `id_dosen_wali` |
| `kode_ruangan → nama_ruangan, kapasitas` | Tabel `ruangan`; kelas menyimpan `id_ruangan` |
| `{tahun, semester} → is_aktif` | Tabel `tahun_akademik`; kelas menyimpan `id_ta` |
| `nilai_akhir → huruf → bobot` | Tabel `skala_nilai` sebagai acuan rentang; huruf dan bobot tidak disimpan |
| `tugas, uts, uas → nilai_akhir` (turunan) | Tidak disimpan; dihitung di view `v_nilai_akhir` |
| `username, password, role` | Tabel `users` terpisah sehingga data login tidak tercampur dengan biodata |

Hasil akhirnya adalah 12 tabel pada ERD Gambar 4.1. Pada skema ini setiap atribut non-kunci bergantung pada kunci, seluruh kunci, dan hanya kunci. Untuk dependensi yang teridentifikasi di atas, setiap determinan juga merupakan kunci kandidat (`id_*` atau kolom `UNIQUE`), sehingga skema juga memenuhi BCNF. Nilai akhir, huruf mutu, IPS, dan IPK sengaja tidak disimpan karena merupakan data turunan. Jika disimpan, nilainya bisa tidak sinkron ketika dosen mengubah komponen nilai atau ketika skala nilai diubah. Sebagai gantinya, nilai-nilai tersebut dihitung oleh view saat dibutuhkan.

![Gambar 4.3 Data gabungan (bentuk tidak normal) yang direkonstruksi dari tabel 3NF dengan JOIN tujuh objek](screenshots/terminal/T16-data-unf.png)

Gambar 4.3 menunjukkan bahwa dekomposisi bersifat *lossless*: data KHS gabungan dapat dibentuk kembali secara utuh dengan `JOIN` antar tabel 3NF.

### 4.3 Contoh query SQL yang dipakai aplikasi

**SELECT dengan JOIN (daftar mahasiswa dan IPK).** Dipakai di halaman Data Mahasiswa (`Mahasiswa::all`).

```sql
SELECT m.npm, m.nama_mahasiswa, p.nama_prodi, d.nama_dosen AS dosen_wali, i.ipk
FROM mahasiswa m
JOIN program_studi p ON p.id_prodi = m.id_prodi
LEFT JOIN dosen d    ON d.id_dosen = m.id_dosen_wali
LEFT JOIN v_ipk i    ON i.id_mahasiswa = m.id_mahasiswa
WHERE m.angkatan = 2025
ORDER BY i.ipk DESC
LIMIT 8;
```

![Gambar 4.4 Hasil query SELECT dengan JOIN](screenshots/terminal/T11-query-join.png)

**SELECT untuk KHS.** Dipakai di halaman KHS mahasiswa (`Krs::khs`), menggabungkan `krs`, `kelas`, `mata_kuliah`, `dosen`, dan view `v_nilai_akhir`.

```sql
SELECT mk.kode_mk, mk.nama_mk, mk.sks, kl.nama_kelas, d.nama_dosen,
       v.nilai_tugas, v.nilai_uts, v.nilai_uas, v.nilai_akhir, v.huruf, v.bobot,
       v.sks * v.bobot AS mutu
FROM krs k
JOIN kelas kl         ON kl.id_kelas = k.id_kelas
JOIN mata_kuliah mk   ON mk.id_mk = kl.id_mk
JOIN dosen d          ON d.id_dosen = kl.id_dosen
LEFT JOIN v_nilai_akhir v ON v.id_krs = k.id_krs
WHERE k.id_mahasiswa = ? AND kl.id_ta = ? AND k.status = 'Disetujui'
ORDER BY mk.kode_mk;
```

![Gambar 4.5 Hasil query KHS, IPS, dan IPK untuk satu mahasiswa](screenshots/terminal/T12-query-khs.png)

**VIEW untuk nilai akhir dan IPK.** Bagian dari `database/schema.sql`.

```sql
CREATE VIEW v_nilai_akhir AS
SELECT n.id_nilai, n.id_krs, k.id_mahasiswa, k.id_kelas, kl.id_ta, mk.id_mk,
       mk.kode_mk, mk.nama_mk, mk.sks, n.nilai_tugas, n.nilai_uts, n.nilai_uas,
       ROUND(0.30 * n.nilai_tugas + 0.30 * n.nilai_uts + 0.40 * n.nilai_uas, 2) AS nilai_akhir,
       s.huruf, s.bobot
FROM nilai n
JOIN krs k          ON k.id_krs = n.id_krs
JOIN kelas kl       ON kl.id_kelas = k.id_kelas
JOIN mata_kuliah mk ON mk.id_mk = kl.id_mk
LEFT JOIN skala_nilai s
  ON ROUND(0.30 * n.nilai_tugas + 0.30 * n.nilai_uts + 0.40 * n.nilai_uas, 2) >= s.batas_bawah
 AND ROUND(0.30 * n.nilai_tugas + 0.30 * n.nilai_uts + 0.40 * n.nilai_uas, 2) <  s.batas_atas
WHERE k.status = 'Disetujui';

CREATE VIEW v_ipk AS
SELECT id_mahasiswa, SUM(sks) AS total_sks, ROUND(SUM(sks * bobot) / SUM(sks), 2) AS ipk
FROM (SELECT id_mahasiswa, id_mk, MAX(sks) AS sks, MAX(bobot) AS bobot
      FROM v_nilai_akhir WHERE huruf IS NOT NULL
      GROUP BY id_mahasiswa, id_mk) terbaik
GROUP BY id_mahasiswa;
```

**GROUP BY dan HAVING (statistik).** Dipakai di dashboard administrator.

```sql
SELECT p.nama_prodi, COUNT(m.id_mahasiswa) AS jumlah_mhs, ROUND(AVG(i.ipk), 2) AS rata_ipk
FROM program_studi p
LEFT JOIN mahasiswa m ON m.id_prodi = p.id_prodi
LEFT JOIN v_ipk i     ON i.id_mahasiswa = m.id_mahasiswa
GROUP BY p.id_prodi
HAVING jumlah_mhs > 0;
```

![Gambar 4.6 Hasil query agregat GROUP BY dan HAVING](screenshots/terminal/T13-query-agregat.png)

**INSERT ... ON DUPLICATE KEY UPDATE (simpan nilai).** Dipakai saat dosen menyimpan nilai satu kelas (`Nilai::simpanKelas`); baris baru dibuat jika belum ada, dan diperbarui jika sudah ada.

```sql
INSERT INTO nilai (id_krs, nilai_tugas, nilai_uts, nilai_uas)
VALUES (?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
    nilai_tugas = VALUES(nilai_tugas),
    nilai_uts   = VALUES(nilai_uts),
    nilai_uas   = VALUES(nilai_uas);
```

**UPDATE dengan JOIN (persetujuan KRS oleh dosen wali).** Kondisi `m.id_dosen_wali = ?` memastikan dosen hanya dapat menyetujui KRS mahasiswa bimbingannya.

```sql
UPDATE krs k
JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
JOIN kelas kl    ON kl.id_kelas = k.id_kelas
SET k.status = 'Disetujui'
WHERE k.id_mahasiswa = ? AND m.id_dosen_wali = ? AND kl.id_ta = ? AND k.status = 'Diajukan';
```

**DELETE bersyarat (batal KRS).** Mahasiswa hanya dapat membatalkan KRS miliknya yang belum disetujui.

```sql
DELETE FROM krs
WHERE id_krs = ? AND id_mahasiswa = ? AND status IN ('Diajukan', 'Ditolak');
```

**SELECT ... FOR UPDATE (cek kuota dalam transaksi).** Saat mahasiswa mengambil kelas, baris kelas dikunci agar dua permintaan bersamaan tidak melampaui kuota.

```sql
SELECT kl.*, mk.sks, mk.id_prodi, t.is_aktif
FROM kelas kl
JOIN mata_kuliah mk   ON mk.id_mk = kl.id_mk
JOIN tahun_akademik t ON t.id_ta = kl.id_ta
WHERE kl.id_kelas = ?
FOR UPDATE;
```

**Deteksi bentrok jadwal.** Dua rentang waktu beririsan jika `mulai_A < selesai_B` dan `selesai_A > mulai_B`.

```sql
SELECT kl.id_kelas, mk.nama_mk, kl.hari, kl.jam_mulai, kl.jam_selesai
FROM kelas kl JOIN mata_kuliah mk ON mk.id_mk = kl.id_mk
WHERE kl.id_ta = ? AND kl.hari = ?
  AND kl.jam_mulai < ? AND kl.jam_selesai > ?
  AND (kl.id_dosen = ? OR kl.id_ruangan = ?)
  AND kl.id_kelas <> ?
LIMIT 1;
```

**Transaksi INSERT, UPDATE, DELETE.** Gambar 4.7 memperlihatkan ketiga perintah dijalankan dalam satu transaksi lalu dibatalkan dengan `ROLLBACK` sehingga data asli tidak berubah.

![Gambar 4.7 INSERT, UPDATE, dan DELETE dalam transaksi yang di-ROLLBACK](screenshots/terminal/T14-insert-update-delete.png)

---

## 5. Laporan Implementasi Lokal Web Aplikasi

### 5.1 Struktur folder lokal proyek

Di server uji, folder proyek berada di `/home/user/WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK` dan ditautkan ke *document root* Apache sebagai `/var/www/html/siakad`. Pada XAMPP (Windows), folder yang sama cukup disalin ke `C:\xampp\htdocs\siakad\`; pada Laragon ke `C:\laragon\www\siakad\`. Isi folder sama dengan Gambar 2.1.

```text
/var/www/html/siakad  ->  WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK/
├── index.php          (front controller)
├── .htaccess
├── app/               (core, controllers, models, views, routes.php)
├── assets/            (css, js, img)
├── config/            (config.php, config.local.php)
├── database/          (schema.sql, seed.sql)
├── docs/              (laporan, screenshot, diagram)
└── tools/             (pembuat data contoh dan skrip dokumentasi)
```

### 5.2 Langkah menjalankan aplikasi secara lokal

Langkah yang dipakai di server uji (Ubuntu):

```bash
# 1. Pasang paket
sudo apt-get install -y apache2 libapache2-mod-php8.3 mariadb-server

# 2. Jalankan layanan Apache dan MariaDB
sudo service mariadb start
sudo service apache2 start

# 3. Buat basis data, tabel, view, dan data contoh
sudo mariadb < database/schema.sql
sudo mariadb < database/seed.sql

# 4. Buat user basis data khusus aplikasi
sudo mariadb -e "CREATE USER 'siakad_app'@'localhost' IDENTIFIED BY 'password-anda';
                 GRANT SELECT, INSERT, UPDATE, DELETE ON db_siakad.* TO 'siakad_app'@'localhost';"
cp config/config.local.example.php config/config.local.php   # isi user & password

# 5. Hubungkan ke Apache (lihat bagian 1.3), lalu buka browser:
#    http://localhost/siakad/
```

Langkah yang setara di XAMPP: jalankan **Apache** dan **MySQL** dari XAMPP Control Panel, buka `http://localhost/phpmyadmin`, impor `database/schema.sql` lalu `database/seed.sql`, salin folder proyek ke `htdocs/siakad`, kemudian buka `http://localhost/siakad/`. Konfigurasi bawaan (`root` tanpa password) sudah cocok dengan XAMPP sehingga `config.local.php` tidak wajib dibuat.

Akun demo:

| Peran | Username | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Dosen (Nurul Fadhilah, dosen wali dan pengampu Manajemen dan Pemodelan Data) | `0011028108` | `dosen123` |
| Mahasiswa (Rahmi Azzahra, Informatika 2025) | `2508107010001` | `mhs123` |

### 5.3 Screenshot halaman utama aplikasi

Screenshot diambil secara otomatis dengan Playwright (`tools/screenshot.js`) yang menjalankan skenario nyata secara berurutan: login, administrator mengelola data, mahasiswa mengisi KRS, dosen wali menyetujui KRS, dosen menginput nilai, dan mahasiswa melihat hasil studinya.

**Halaman login**

![Gambar 5.1 Halaman login dengan daftar akun demo](screenshots/01-login.png)

![Gambar 5.2 Login gagal karena password salah](screenshots/02-login-gagal.png)

**Administrator**

![Gambar 5.3 Dashboard administrator: ringkasan data, jumlah mahasiswa per prodi, dan sebaran huruf mutu](screenshots/03-admin-dashboard.png)

![Gambar 5.4 Data fakultas](screenshots/04-admin-fakultas.png)

![Gambar 5.5 Data program studi beserta jumlah dosen dan mahasiswa](screenshots/05-admin-prodi.png)

![Gambar 5.6 Data dosen](screenshots/06-admin-dosen.png)

![Gambar 5.7 Data mahasiswa dengan dosen wali, total SKS, dan IPK](screenshots/07-admin-mahasiswa.png)

![Gambar 5.8 Filter data mahasiswa berdasarkan prodi Statistika angkatan 2025](screenshots/08-admin-mahasiswa-filter.png)

![Gambar 5.9 Data ruangan](screenshots/19-admin-ruangan.png)

![Gambar 5.10 Tahun akademik; hanya satu yang aktif](screenshots/20-admin-tahun-akademik.png)

![Gambar 5.11 Kelas dan jadwal kuliah semester 2026/2027 Ganjil prodi Informatika](screenshots/21-admin-kelas.png)

![Gambar 5.12 Manajemen pengguna: reset password dan aktif/nonaktif akun](screenshots/23-admin-pengguna.png)

**Dosen**

![Gambar 5.13 Dashboard dosen: jadwal mengajar dan ringkasan perwalian](screenshots/28-dosen-dashboard.png)

![Gambar 5.14 Daftar kelas yang diampu dosen](screenshots/32-dosen-kelas.png)

**Mahasiswa**

![Gambar 5.15 Dashboard mahasiswa: IPK, SKS, jadwal kuliah, dan riwayat IPS](screenshots/25-mhs-dashboard.png)

![Gambar 5.16 Tampilan responsif di layar ponsel (lebar 390 px)](screenshots/42-mobile-dashboard-mahasiswa.png)

### 5.4 Penjelasan alur fungsi utama sistem

![Gambar 5.17 Alur fungsi utama SIAKAD](diagram/03-alur-sistem.png)

**Administrator menambah dan mengedit data.** Administrator mengelola data master. Gambar 5.18 sampai 5.21 memperlihatkan penambahan mahasiswa baru: formulir yang diisi salah ditolak oleh validasi sisi server (NPM harus 13 digit, format email, field wajib), lalu setelah diperbaiki data tersimpan dan akun login dengan username NPM dibuat otomatis dalam satu transaksi. Gambar 5.22 sampai 5.27 memperlihatkan siklus CRUD lengkap pada mata kuliah: tambah INF306 Data Warehouse, ubah nama dan semesternya, hapus, lalu percobaan menghapus INF301 yang ditolak karena masih dipakai oleh kelas (foreign key). Gambar 5.28 memperlihatkan sistem menolak kelas baru karena dosen yang sama sudah mengajar pada hari dan jam yang beririsan.

![Gambar 5.18 Validasi sisi server saat menambah mahasiswa](screenshots/09-admin-mahasiswa-validasi.png)

![Gambar 5.19 Formulir tambah mahasiswa yang sudah benar](screenshots/10-admin-mahasiswa-form-tambah.png)

![Gambar 5.20 Mahasiswa berhasil ditambahkan beserta akun loginnya](screenshots/11-admin-mahasiswa-tambah-berhasil.png)

![Gambar 5.21 Daftar mata kuliah prodi Informatika](screenshots/12-admin-mata-kuliah.png)

![Gambar 5.22 Create: formulir tambah mata kuliah](screenshots/13-admin-mk-form-tambah.png)

![Gambar 5.23 Create berhasil: mata kuliah INF306 tersimpan](screenshots/14-admin-mk-tambah-berhasil.png)

![Gambar 5.24 Update: formulir ubah mata kuliah](screenshots/15-admin-mk-form-ubah.png)

![Gambar 5.25 Update berhasil](screenshots/16-admin-mk-ubah-berhasil.png)

![Gambar 5.26 Delete berhasil](screenshots/17-admin-mk-hapus-berhasil.png)

![Gambar 5.27 Delete ditolak karena mata kuliah masih dipakai kelas (foreign key RESTRICT)](screenshots/18-admin-mk-hapus-ditolak-fk.png)

![Gambar 5.28 Penambahan kelas ditolak karena jadwal dosen bentrok](screenshots/22-admin-kelas-bentrok.png)

**Mahasiswa mengisi KRS.** Mahasiswa memilih kelas yang ditawarkan pada tahun akademik aktif. Sebelum menyimpan, sistem memeriksa di dalam satu transaksi bahwa kelas berada di prodi mahasiswa, mata kuliah belum diambil, kuota masih tersedia, jadwal tidak bentrok, dan total SKS tidak melebihi 24. KRS baru berstatus *Diajukan* dan masih dapat dibatalkan.

![Gambar 5.29 KRS sebelum menambah mata kuliah (4 mata kuliah, 12 SKS)](screenshots/26-mhs-krs-sebelum.png)

![Gambar 5.30 Mahasiswa mengambil Rekayasa Perangkat Lunak; total menjadi 15 SKS dengan status Diajukan](screenshots/27-mhs-krs-ambil-berhasil.png)

**Dosen wali menyetujui KRS.** Dosen melihat daftar mahasiswa bimbingan beserta status KRS-nya, memeriksa rincian, lalu menyetujui atau menolak.

![Gambar 5.31 Daftar mahasiswa bimbingan dan status KRS](screenshots/29-dosen-perwalian.png)

![Gambar 5.32 Rincian KRS yang diajukan mahasiswa](screenshots/30-dosen-perwalian-detail.png)

![Gambar 5.33 KRS disetujui dosen wali](screenshots/31-dosen-perwalian-disetujui.png)

**Dosen menginput nilai.** Dosen pengampu membuka kelas, mengisi nilai tugas, UTS, dan UAS. Nilai akhir dan huruf mutu langsung dihitung di browser oleh JavaScript sebagai pratinjau, sedangkan nilai resmi tetap dihitung ulang oleh view di basis data. Nilai di luar rentang 0 sampai 100 ditolak oleh validasi server (Gambar 5.35) dan juga oleh `CHECK` di basis data.

![Gambar 5.34 Formulir input nilai dengan pratinjau nilai akhir dan huruf mutu](screenshots/33-dosen-input-nilai-form.png)

![Gambar 5.35 Nilai UAS 150 ditolak validasi](screenshots/34-dosen-input-nilai-validasi.png)

![Gambar 5.36 Nilai tersimpan; 8 dari 16 mahasiswa sudah memiliki nilai lengkap](screenshots/35-dosen-input-nilai-tersimpan.png)

**Mahasiswa melihat nilai.** Setelah KRS disetujui dan nilai diinput, mahasiswa dapat melihat KHS per semester (dengan IPS) serta transkrip (dengan IPK). Pada Gambar 5.39 dan 5.40 IPK Rahmi Azzahra adalah 2,91 dari 27 SKS, sesuai perhitungan manual: total mutu 78,50 dibagi 27 SKS sama dengan 2,907, dibulatkan menjadi 2,91.

![Gambar 5.37 Status KRS mahasiswa setelah disetujui (terkunci, tidak dapat dibatalkan)](screenshots/36-mhs-krs-disetujui.png)

![Gambar 5.38 KHS semester 2025/2026 Genap dengan IPS](screenshots/37-mhs-khs-semester-genap.png)

![Gambar 5.39 KHS semester berjalan: Manajemen dan Pemodelan Data sudah bernilai AB (82,60), mata kuliah lain belum dinilai sehingga IPS ditandai sementara](screenshots/38-mhs-khs-semester-berjalan.png)

![Gambar 5.40 Transkrip nilai sementara dan IPK](screenshots/39-mhs-transkrip.png)

**Kontrol akses dan profil.** Mahasiswa yang mencoba membuka halaman administrator mendapat respons 403. Semua pengguna dapat mengganti password sendiri.

![Gambar 5.41 Akses ditolak (403) saat mahasiswa membuka halaman administrator](screenshots/40-mhs-akses-ditolak-403.png)

![Gambar 5.42 Halaman profil dan ganti password](screenshots/41-profil-ganti-password.png)

### 5.5 Kendala teknis dan solusi selama implementasi

Kendala berikut benar-benar ditemui selama pembangunan aplikasi di server uji.

| No | Kendala | Penyebab | Solusi |
|---|---|---|---|
| 1 | Setelah `apt-get install`, Apache dan MariaDB tidak otomatis berjalan (pesan `policy-rc.d denied execution`) | Lingkungan server melarang skrip instalasi menjalankan layanan | Layanan dijalankan manual dengan `service mariadb start` dan `service apache2 start` |
| 2 | Peringatan `AH00558: Could not reliably determine the server's fully qualified domain name` | Apache tidak mengetahui nama server | Menambahkan `ServerName localhost` di `siakad.conf` |
| 3 | PHP tidak dapat login ke MariaDB sebagai `root` | Di Ubuntu, `root` MariaDB memakai autentikasi `unix_socket`, sedangkan Apache berjalan sebagai user `www-data` | Membuat user `siakad_app` dengan hak terbatas dan menyimpan kredensialnya di `config.local.php` yang tidak ikut di-commit |
| 4 | Halaman galat untuk token CSRF yang kedaluwarsa muncul sebagai HTTP 500 | Kode status 419 (dipakai sebagian framework) tidak dikenal Apache sehingga diubah menjadi 500 | Memakai kode standar 403 dengan judul "Sesi Kedaluwarsa" |
| 5 | Folder `config/` dan berkas `.sql` berpotensi dapat diunduh lewat browser | Seluruh folder proyek berada di bawah *document root* | Menambahkan `.htaccess` (aturan `RewriteRule ... [F]` dan `Require all denied`) serta `AllowOverride All`; diverifikasi dengan `curl` menghasilkan 403 |
| 6 | IPK berpotensi salah jika mahasiswa mengulang mata kuliah (SKS terhitung dua kali) | View IPK awal menjumlahkan semua nilai | View `v_ipk` diubah agar memilih bobot terbaik per mata kuliah; diuji dengan transaksi yang di-ROLLBACK (IPK 2,91 menjadi 3,06 dengan total SKS tetap 27) |
| 7 | Validasi sisi server sulit diperagakan karena browser lebih dulu menolak input | Atribut HTML5 `required`, `maxlength`, `type=email` | Validasi dibuat berlapis (HTML5, PHP, dan `CHECK` di basis data); untuk dokumentasi, validasi HTML5 dimatikan sementara oleh skrip uji agar validasi server terlihat |

---

## 6. Hasil Web Aplikasi (Localhost)

### 6.1 Lingkungan hasil implementasi

Aplikasi berjalan di **localhost** pada server Apache 2.4.58 dengan PHP 8.3.6 dan MariaDB 10.11.14, dan diakses melalui `http://localhost/siakad/`. Bukti teknisnya adalah keluaran `curl -I` (HTTP 200 dari `Server: Apache/2.4.58 (Ubuntu)`, Gambar 1.5), halaman Informasi Sistem (Gambar 3.2) yang membaca versi web server, PHP, dan RDBMS langsung dari aplikasi, serta seluruh screenshot pada bagian 5. Kode sumber disimpan di GitHub pada repositori yang tercantum di halaman judul.

### 6.2 Screenshot halaman utama dan fitur utama

| Fitur | Gambar |
|---|---|
| Login dan login gagal | 5.1, 5.2 |
| Dashboard administrator, dosen, mahasiswa | 5.3, 5.13, 5.15 |
| CRUD data (create, read, update, delete, validasi, tolak FK) | 5.18 sampai 5.27 |
| Kelas dan jadwal dengan deteksi bentrok | 5.11, 5.28 |
| KRS mahasiswa | 5.29, 5.30, 5.37 |
| Persetujuan KRS oleh dosen wali | 5.31 sampai 5.33 |
| Input nilai oleh dosen | 5.34 sampai 5.36 |
| KHS dan transkrip | 5.38 sampai 5.40 |
| Kontrol akses berbasis peran | 5.41 |
| Tampilan ponsel | 5.16 |

### 6.3 Alur fungsi sistem per peran

| Peran | Hak akses dan fungsi |
|---|---|
| Administrator | Mengelola fakultas, program studi, dosen, mahasiswa, mata kuliah, ruangan, tahun akademik, kelas dan jadwal; membuat akun administrator; mereset password dan menonaktifkan akun; melihat statistik dan informasi sistem |
| Dosen | Melihat jadwal mengajar; menginput dan memperbarui nilai tugas, UTS, UAS hanya untuk kelas yang diampunya; sebagai dosen wali memeriksa, menyetujui, atau menolak KRS mahasiswa bimbingannya |
| Mahasiswa | Mengisi dan membatalkan KRS pada tahun akademik aktif; melihat status persetujuan; melihat KHS per semester, IPS, transkrip, dan IPK |

Alur utamanya mengikuti Gambar 5.17: administrator menyiapkan data dan kelas, mahasiswa mengajukan KRS, dosen wali menyetujui, dosen pengampu menginput nilai, sistem menghitung nilai akhir, IPS, dan IPK melalui view, lalu mahasiswa melihat hasilnya.

### 6.4 Kendala teknis dan solusi saat mengunggah ke server

Pemasangan dilakukan di server lokal. Kendala yang dihadapi saat memindahkan kode ke *document root* Apache dan menghubungkannya dengan basis data sudah dirinci pada bagian 5.5 (layanan tidak berjalan otomatis, peringatan ServerName, autentikasi `root` MariaDB, perlindungan folder konfigurasi). Beberapa hal tambahan yang perlu diperhatikan jika aplikasi diunggah ke hosting PHP publik (misalnya InfinityFree): GitHub Pages dan Vercel versi statis tidak menjalankan PHP sehingga tidak dapat dipakai untuk aplikasi ini; nama basis data dan user pada hosting gratis biasanya diberi awalan otomatis sehingga nilai di `config.local.php` harus disesuaikan; impor `schema.sql` perlu menghapus baris `CREATE DATABASE` dan `USE` karena basis data dibuat lewat panel hosting; dan `DROP VIEW`/`CREATE VIEW` memerlukan hak `CREATE VIEW` yang tidak selalu tersedia di paket gratis. Aplikasi menghitung URL dasarnya secara otomatis dari lokasi `index.php`, sehingga tidak perlu mengubah kode ketika folder atau domain berbeda.

---

## Lampiran

**A. Berkas penting di repositori**

| Berkas | Keterangan |
|---|---|
| `database/schema.sql` | DDL lengkap basis data |
| `database/seed.sql` | Data contoh fiktif |
| `tools/generate_seed.php` | Pembuat `seed.sql` secara deterministik |
| `tools/screenshot.js` | Skenario screenshot otomatis (Playwright) |
| `tools/capture_terminal.py`, `tools/screenshot_terminal.js` | Pengambil keluaran terminal untuk laporan |
| `docs/diagram/src/*.dot` | Sumber diagram Graphviz |
| `docs/terminal/*.txt` | Keluaran asli perintah terminal dalam bentuk teks |

**B. Pengujian yang telah dilakukan**

Seluruh halaman diuji untuk ketiga peran dan semuanya mengembalikan HTTP 200 tanpa peringatan PHP. Akses lintas peran mengembalikan 403, aksi hapus melalui GET mengembalikan 405, POST tanpa token CSRF ditolak, dan halaman yang tidak ada mengembalikan 404. Alur data yang diuji meliputi CRUD mata kuliah dan mahasiswa, penolakan UNIQUE dan FK, deteksi bentrok jadwal, validasi tahun akademik, pengambilan dan pembatalan KRS (termasuk penolakan untuk mata kuliah ganda, kelas di luar tahun aktif, dan kelas prodi lain), persetujuan KRS oleh dosen wali, penolakan akses dosen ke kelas yang bukan miliknya, serta penyimpanan nilai beserta perhitungan huruf mutu (contoh: 30% × 85 + 30% × 78,5 + 40% × 90 = 85,05, huruf AB).

**C. Skala nilai yang dipakai**

| Huruf | Rentang nilai akhir | Bobot |
|---|---|---|
| A | 87 – 100 | 4,00 |
| AB | 78 – < 87 | 3,50 |
| B | 69 – < 78 | 3,00 |
| BC | 60 – < 69 | 2,50 |
| C | 51 – < 60 | 2,00 |
| D | 41 – < 51 | 1,00 |
| E | < 41 | 0,00 |

Skala disimpan di tabel `skala_nilai` sehingga dapat disesuaikan dengan peraturan akademik yang berlaku tanpa mengubah kode program.
