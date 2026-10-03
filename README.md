# SIAKAD: Web Aplikasi Sistem Informasi Perkuliahan

Proyek UTS mata kuliah **Manajemen dan Pemodelan Data**. Aplikasi web sistem informasi perkuliahan dengan tiga peran (administrator, dosen, mahasiswa), dibangun dengan PHP 8 native (pola MVC, PDO) dan MySQL/MariaDB.

Laporan UTS lengkap (nomor 1 sampai 6, dengan screenshot dan diagram): [`docs/LAPORAN_UTS.md`](docs/LAPORAN_UTS.md), juga tersedia sebagai [`PDF`](docs/LAPORAN_UTS.pdf) dan [`DOCX`](docs/LAPORAN_UTS.docx).

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
.htaccess            aturan Apache
app/core/            Database (PDO), Model, Controller, Auth, helpers
app/models/          query SQL per entitas
app/controllers/     logika per modul
app/views/           template HTML
app/routes.php       daftar halaman dan peran
assets/              CSS, JS, logo
config/              konfigurasi
database/            schema.sql, seed.sql
docs/                laporan, screenshot, diagram, keluaran terminal
tools/               pembuat data contoh, skrip screenshot dan dokumentasi
```

## Membuat ulang dokumentasi

```bash
php tools/generate_seed.php                                   # database/seed.sql
mariadb < database/schema.sql && mariadb < database/seed.sql  # reset data
NODE_PATH=$(npm root -g) node tools/screenshot.js http://localhost/siakad
python3 tools/capture_terminal.py
NODE_PATH=$(npm root -g) node tools/screenshot_terminal.js
bash tools/build_report.sh                                    # PDF dan DOCX laporan
```
