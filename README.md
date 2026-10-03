# SIAKAD: Web Aplikasi Sistem Informasi Perkuliahan

Proyek UTS mata kuliah **Manajemen dan Pemodelan Data**. Aplikasi web sistem informasi perkuliahan dengan tiga peran (administrator, dosen, mahasiswa), dibangun dengan PHP 8 native (pola MVC, PDO) dan MySQL/MariaDB.

[![Buka di GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/barata90/WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK?quickstart=1)

**Menjalankan aplikasi dengan sekali klik:** tekan tombol *Open in GitHub Codespaces* di atas, login GitHub, lalu tunggu sekitar 2 sampai 3 menit pada pembukaan pertama. Codespaces membuat server PHP 8.3 + Apache + MariaDB 10.11, mengimpor basis data beserta data contoh secara otomatis, lalu membuka aplikasi di tab baru. Jika tab tidak terbuka sendiri, buka panel **Ports** di Codespaces dan klik ikon globe pada port 80.

## Tautan

| | |
|---|---|
| Aplikasi online (GitHub Codespaces) | <https://codespaces.new/barata90/WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK?quickstart=1> |
| Halaman proyek (GitHub Pages) | <https://barata90.github.io/WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK/> |
| Repositori kode | <https://github.com/barata90/WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK> |
| Aplikasi di komputer sendiri | <http://localhost/siakad/> (XAMPP di Windows/macOS) atau <http://localhost:8000> (`php -S`) |
| Laporan UTS | [Markdown](docs/LAPORAN_UTS.md) · [PDF](docs/LAPORAN_UTS.pdf) · [DOCX](docs/LAPORAN_UTS.docx) |
| Galeri tangkapan layar | [docs/screenshots](docs/screenshots) |

GitHub hanya menyimpan kode, dan GitHub Pages hanya dapat menampilkan halaman statis. Karena itu `index.html` berfungsi sebagai halaman proyek (ringkasan, tangkapan layar, akun demo, dan tombol menjalankan aplikasi), sedangkan aplikasinya sendiri (`index.php`) memerlukan PHP dan MySQL sehingga dijalankan lewat Codespaces atau di komputer sendiri. Alamat `localhost` hanya terbuka di komputer yang sedang menjalankan server tersebut.

> Halaman GitHub Pages perlu diaktifkan sekali oleh pemilik repositori: **Settings → Pages → Build and deployment → Source: Deploy from a branch → Branch: `main` / `(root)` → Save**. Setelah 1 sampai 2 menit, halaman tersedia di alamat di atas.

![Dashboard administrator](docs/screenshots/03-admin-dashboard.png)

## Fitur

| Peran | Fitur |
|---|---|
| Administrator | CRUD fakultas, program studi, dosen, mahasiswa, mata kuliah, ruangan, tahun akademik, kelas dan jadwal (dengan deteksi bentrok dosen/ruangan); manajemen akun; dashboard statistik; halaman informasi server dan basis data |
| Dosen | Jadwal mengajar; input nilai tugas, UTS, UAS dengan pratinjau nilai akhir; persetujuan KRS mahasiswa bimbingan (dosen wali) |
| Mahasiswa | Pengisian KRS (cek kuota, mata kuliah ganda, bentrok jadwal, maksimal 24 SKS); KHS per semester dengan IPS; transkrip dengan IPK |

Keamanan: password bcrypt, prepared statement, token CSRF, escaping output, `session_regenerate_id`, kontrol akses berbasis peran, pemeriksaan kepemilikan data, dan `.htaccess` yang memblokir folder internal.

## Basis data

`db_siakad` berisi 12 tabel InnoDB dalam bentuk normal ketiga (3NF) dan 3 view (`v_nilai_akhir`, `v_ips`, `v_ipk`). Nilai akhir = 30% tugas + 30% UTS + 40% UAS, dikonversi ke huruf mutu melalui tabel `skala_nilai`.

![ERD](docs/diagram/02-erd.png)

## Menjalankan secara lokal

**XAMPP / Laragon (Windows)**

1. Salin folder proyek ke `C:\xampp\htdocs\siakad` (atau `C:\laragon\www\siakad`).
2. Jalankan Apache dan MySQL dari control panel.
3. Buka `http://localhost/phpmyadmin`, impor `database/schema.sql`, lalu `database/seed.sql`.
4. Buka `http://localhost/siakad/`.

Konfigurasi bawaan memakai user `root` tanpa password. Jika berbeda, salin `config/config.local.example.php` menjadi `config/config.local.php` lalu sesuaikan.

**MacBook dengan XAMPP (disarankan, termasuk macOS 12 Monterey)**

1. Pasang [XAMPP for OS X](https://www.apachefriends.org), buka `/Applications/XAMPP/manager-osx`, lalu di tab *Manage Servers* jalankan **MySQL Database** dan **Apache Web Server** satu per satu (ProFTPD tidak diperlukan).
2. Unduh repositori ini (**Code → Download ZIP**), ekstrak, lalu salin foldernya ke `/Applications/XAMPP/xamppfiles/htdocs/` dan **ganti namanya menjadi `siakad`**. Hasil akhirnya: `/Applications/XAMPP/xamppfiles/htdocs/siakad/index.php`.
3. Buka <http://localhost/phpmyadmin>, tab **Import**: impor `database/schema.sql` terlebih dahulu, lalu `database/seed.sql`.
4. Buka <http://localhost/siakad/> dan login dengan `admin` / `admin123`.

Konfigurasi bawaan (user `root` tanpa password) sudah cocok dengan XAMPP. Jika folder tidak diganti namanya, alamatnya mengikuti nama folder, misalnya `http://localhost/WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK-main/`. Jika phpMyAdmin menampilkan galat `mysqli::real_connect(): (HY000/2002)`, artinya MySQL belum menyala; jalankan `sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start` di Terminal untuk melihat pesan galatnya.

**MacBook dengan Homebrew (macOS yang masih didukung Homebrew)**

```bash
# 1. Pasang PHP dan MariaDB (sekali saja). Homebrew: https://brew.sh
brew install php mariadb
brew services start mariadb

# 2. Masuk ke folder proyek, lalu buat basis data, data contoh, dan user aplikasi
cd ~/Downloads/WEB-APLIKASI-SISTEM-INFORMASI-PERKULIAHAN-USK
sudo mariadb < database/schema.sql
sudo mariadb < database/seed.sql
sudo mariadb -e "CREATE USER IF NOT EXISTS 'siakad'@'localhost' IDENTIFIED BY 'siakad123';
                 GRANT ALL PRIVILEGES ON db_siakad.* TO 'siakad'@'localhost';"

# 3. Arahkan aplikasi ke basis data lokal
cat > config/config.local.php <<'PHP'
<?php
return ['db_host' => 'localhost', 'db_user' => 'siakad', 'db_pass' => 'siakad123', 'debug' => true];
PHP

# 4. Jalankan server bawaan PHP, lalu buka http://localhost:8000
php -S localhost:8000 tools/router.php
```

`tools/router.php` meniru aturan `.htaccess`, sehingga folder `config/`, `database/`, dan `app/` tetap tidak dapat diakses lewat browser. Homebrew tidak lagi menyediakan paket siap pakai untuk macOS 12 sehingga instalasi di versi tersebut mencoba mengompilasi puluhan paket dan sering gagal; gunakan XAMPP di atas. Alternatif tanpa terminal adalah [MAMP](https://www.mamp.info/): salin folder proyek ke `/Applications/MAMP/htdocs/siakad`, impor SQL lewat phpMyAdmin MAMP, isi `config.local.php` dengan `db_port` 8889 serta user dan password `root`, lalu buka `http://localhost:8888/siakad/`.

**phpMyAdmin di server bersama (dba.dotdigital.id)**

Untuk bagian basis data (impor, struktur tabel, ERD Designer, dan query di phpMyAdmin), gunakan berkas `database/siakad_phpmyadmin.sql`:

1. Login ke phpMyAdmin, buka **Databases**, buat basis data **baru** yang kosong, misalnya `siakad_uts` dengan collation `utf8mb4_unicode_ci`. Jangan memakai basis data tugas lain seperti `siakad_usk_praktik`, karena nama tabelnya (`mahasiswa`, `dosen`, `mata_kuliah`, `krs`) sama.
2. Pilih basis data tersebut, buka tab **Import**, pilih `database/siakad_phpmyadmin.sql`, lalu klik **Import**.

Berkas ini tidak berisi `CREATE DATABASE`, `USE`, `DROP`, maupun `TRUNCATE`, sehingga tidak menghapus tabel lain di akun yang sama. Jika ada tabel bernama sama, impor berhenti dengan galat tanpa menghapus data.

Aplikasi web (PHP) tidak dapat langsung memakai basis data di dba.dotdigital.id dari MacBook atau Codespaces. Alamat tersebut hanya melayani halaman phpMyAdmin lewat HTTPS (di belakang Cloudflare), sedangkan port MySQL (3306) tidak terbuka untuk koneksi dari luar. Jika pengelola server memberikan host dan port MySQL yang dapat diakses, isi nilainya di `config/config.local.php` (`db_host`, `db_port`, `db_name`, `db_user`, `db_pass`). Berkas tersebut tercantum di `.gitignore`, jadi password tidak ikut ter-upload ke GitHub. **Jangan menulis password basis data di README atau berkas lain yang di-commit.**

**Linux (Apache + MariaDB)**

```bash
sudo apt-get install -y apache2 libapache2-mod-php mariadb-server
sudo service mariadb start && sudo service apache2 start
sudo mariadb < database/schema.sql && sudo mariadb < database/seed.sql
sudo mariadb -e "CREATE USER 'siakad_app'@'localhost' IDENTIFIED BY 'ganti-password';
                 GRANT SELECT, INSERT, UPDATE, DELETE ON db_siakad.* TO 'siakad_app'@'localhost';"
cp config/config.local.example.php config/config.local.php   # isi user & password
sudo ln -s "$PWD" /var/www/html/siakad
sudo a2enmod rewrite && sudo service apache2 reload
# Pastikan AllowOverride All untuk folder ini (contoh di docs/LAPORAN_UTS.md bagian 1.3)
```

## Akun demo

| Peran | Username | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Dosen | `0011028108` | `dosen123` |
| Mahasiswa | `2508107010001` | `mhs123` |

Semua dosen memakai password `dosen123` dan semua mahasiswa `mhs123`. Seluruh nama, NIDN, NPM, dan nilai pada data contoh adalah fiktif.

## Struktur folder

```text
index.php            front controller (routing, akses, CSRF)
index.html           halaman proyek untuk GitHub Pages (statis)
.devcontainer/       konfigurasi GitHub Codespaces (PHP + Apache + MariaDB)
.htaccess            aturan Apache
app/core/            Database (PDO), Model, Controller, Auth, helpers
app/models/          query SQL per entitas
app/controllers/     logika per modul
app/views/           template HTML
app/routes.php       daftar halaman dan peran
assets/              CSS, JS, logo
config/              konfigurasi
database/            schema.sql, seed.sql, siakad_phpmyadmin.sql
docs/                laporan, screenshot, diagram, keluaran terminal
tools/               router.php (server bawaan PHP), pembuat data contoh, skrip dokumentasi
```

## Membuat ulang dokumentasi

```bash
php tools/generate_seed.php                                   # database/seed.sql
python3 tools/build_phpmyadmin_sql.py                         # database/siakad_phpmyadmin.sql
mariadb < database/schema.sql && mariadb < database/seed.sql  # reset data
NODE_PATH=$(npm root -g) node tools/screenshot.js http://localhost/siakad
python3 tools/capture_terminal.py
NODE_PATH=$(npm root -g) node tools/screenshot_terminal.js
bash tools/build_report.sh                                    # PDF dan DOCX laporan
```
