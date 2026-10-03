-- =====================================================================
--  SIAKAD - berkas impor phpMyAdmin (schema + data contoh fiktif)
--  1. Buat basis data BARU yang kosong, mis. siakad_uts (utf8mb4_unicode_ci).
--     Jangan impor ke basis data tugas lain: nama tabelnya bisa sama.
--  2. Pilih basis data tersebut, buka tab Import, pilih berkas ini, klik Import.
--  Akun demo: admin / admin123, dosen 0011028108 / dosen123,
--             mahasiswa 2508107010001 / mhs123
--  Dibuat oleh tools/build_phpmyadmin_sql.py; jangan diedit manual.
-- =====================================================================
SET NAMES utf8mb4;

-- =====================================================================
--  SIAKAD - Sistem Informasi Perkuliahan
--  Skema basis data (DDL) untuk MySQL 8 / MariaDB 10.4+
--  Semua tabel memakai InnoDB agar foreign key dan transaksi berlaku.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Fakultas
-- ---------------------------------------------------------------------
CREATE TABLE fakultas (
  id_fakultas    INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  kode_fakultas  VARCHAR(10)   NOT NULL,
  nama_fakultas  VARCHAR(100)  NOT NULL,
  created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_fakultas),
  UNIQUE KEY uq_fakultas_kode (kode_fakultas)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. Program studi (setiap prodi berada di satu fakultas)
-- ---------------------------------------------------------------------
CREATE TABLE program_studi (
  id_prodi     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  kode_prodi   VARCHAR(10)   NOT NULL,
  nama_prodi   VARCHAR(100)  NOT NULL,
  jenjang      ENUM('D3','S1','S2','S3') NOT NULL DEFAULT 'S1',
  id_fakultas  INT UNSIGNED  NOT NULL,
  created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_prodi),
  UNIQUE KEY uq_prodi_kode (kode_prodi),
  KEY idx_prodi_fakultas (id_fakultas),
  CONSTRAINT fk_prodi_fakultas FOREIGN KEY (id_fakultas)
    REFERENCES fakultas (id_fakultas) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. Akun pengguna (login). Nama orang tidak disimpan di sini agar tidak
--    terjadi duplikasi dengan tabel dosen/mahasiswa.
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id_user        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  username       VARCHAR(30)   NOT NULL,
  password_hash  VARCHAR(255)  NOT NULL,
  role           ENUM('admin','dosen','mahasiswa') NOT NULL,
  is_aktif       TINYINT(1)    NOT NULL DEFAULT 1,
  last_login     DATETIME      NULL,
  created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. Dosen (homebase di satu prodi, punya satu akun login)
-- ---------------------------------------------------------------------
CREATE TABLE dosen (
  id_dosen    INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nidn        CHAR(10)      NOT NULL,
  nama_dosen  VARCHAR(100)  NOT NULL,
  email       VARCHAR(100)  NULL,
  no_hp       VARCHAR(20)   NULL,
  id_prodi    INT UNSIGNED  NOT NULL,
  id_user     INT UNSIGNED  NULL,
  created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_dosen),
  UNIQUE KEY uq_dosen_nidn (nidn),
  UNIQUE KEY uq_dosen_email (email),
  UNIQUE KEY uq_dosen_user (id_user),
  KEY idx_dosen_prodi (id_prodi),
  CONSTRAINT fk_dosen_prodi FOREIGN KEY (id_prodi)
    REFERENCES program_studi (id_prodi) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_dosen_user FOREIGN KEY (id_user)
    REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. Mahasiswa (terdaftar di satu prodi, punya satu dosen wali)
-- ---------------------------------------------------------------------
CREATE TABLE mahasiswa (
  id_mahasiswa    INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  npm             CHAR(13)      NOT NULL,
  nama_mahasiswa  VARCHAR(100)  NOT NULL,
  jenis_kelamin   ENUM('L','P') NOT NULL,
  tanggal_lahir   DATE          NULL,
  email           VARCHAR(100)  NULL,
  angkatan        YEAR          NOT NULL,
  id_prodi        INT UNSIGNED  NOT NULL,
  id_dosen_wali   INT UNSIGNED  NULL,
  id_user         INT UNSIGNED  NULL,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_mahasiswa),
  UNIQUE KEY uq_mhs_npm (npm),
  UNIQUE KEY uq_mhs_email (email),
  UNIQUE KEY uq_mhs_user (id_user),
  KEY idx_mhs_prodi (id_prodi),
  KEY idx_mhs_wali (id_dosen_wali),
  CONSTRAINT fk_mhs_prodi FOREIGN KEY (id_prodi)
    REFERENCES program_studi (id_prodi) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_mhs_wali FOREIGN KEY (id_dosen_wali)
    REFERENCES dosen (id_dosen) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_mhs_user FOREIGN KEY (id_user)
    REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. Mata kuliah (kurikulum milik satu prodi)
-- ---------------------------------------------------------------------
CREATE TABLE mata_kuliah (
  id_mk           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  kode_mk         VARCHAR(10)      NOT NULL,
  nama_mk         VARCHAR(100)     NOT NULL,
  sks             TINYINT UNSIGNED NOT NULL,
  semester_paket  TINYINT UNSIGNED NOT NULL,
  jenis           ENUM('Wajib','Pilihan') NOT NULL DEFAULT 'Wajib',
  id_prodi        INT UNSIGNED     NOT NULL,
  created_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_mk),
  UNIQUE KEY uq_mk_kode (kode_mk),
  KEY idx_mk_prodi (id_prodi),
  CONSTRAINT chk_mk_sks      CHECK (sks BETWEEN 1 AND 6),
  CONSTRAINT chk_mk_semester CHECK (semester_paket BETWEEN 1 AND 8),
  CONSTRAINT fk_mk_prodi FOREIGN KEY (id_prodi)
    REFERENCES program_studi (id_prodi) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. Tahun akademik / semester berjalan
-- ---------------------------------------------------------------------
CREATE TABLE tahun_akademik (
  id_ta       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tahun       CHAR(9)      NOT NULL,              -- contoh: 2026/2027
  semester    ENUM('Ganjil','Genap') NOT NULL,
  is_aktif    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_ta),
  UNIQUE KEY uq_ta (tahun, semester)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. Ruangan kuliah
-- ---------------------------------------------------------------------
CREATE TABLE ruangan (
  id_ruangan    INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  kode_ruangan  VARCHAR(15)       NOT NULL,
  nama_ruangan  VARCHAR(100)      NOT NULL,
  gedung        VARCHAR(100)      NULL,
  kapasitas     SMALLINT UNSIGNED NOT NULL DEFAULT 40,
  created_at    TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_ruangan),
  UNIQUE KEY uq_ruangan_kode (kode_ruangan)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. Kelas = penawaran satu mata kuliah pada satu tahun akademik,
--    diampu satu dosen, di satu ruangan dan jadwal tertentu.
-- ---------------------------------------------------------------------
CREATE TABLE kelas (
  id_kelas     INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  id_mk        INT UNSIGNED      NOT NULL,
  id_ta        INT UNSIGNED      NOT NULL,
  id_dosen     INT UNSIGNED      NOT NULL,
  id_ruangan   INT UNSIGNED      NULL,
  nama_kelas   VARCHAR(5)        NOT NULL,          -- contoh: 01, 02
  hari         ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  jam_mulai    TIME              NOT NULL,
  jam_selesai  TIME              NOT NULL,
  kuota        SMALLINT UNSIGNED NOT NULL DEFAULT 40,
  created_at   TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_kelas),
  UNIQUE KEY uq_kelas (id_mk, id_ta, nama_kelas),
  KEY idx_kelas_ta (id_ta),
  KEY idx_kelas_dosen (id_dosen),
  KEY idx_kelas_ruangan (id_ruangan),
  CONSTRAINT chk_kelas_jam CHECK (jam_selesai > jam_mulai),
  CONSTRAINT fk_kelas_mk FOREIGN KEY (id_mk)
    REFERENCES mata_kuliah (id_mk) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_kelas_ta FOREIGN KEY (id_ta)
    REFERENCES tahun_akademik (id_ta) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_kelas_dosen FOREIGN KEY (id_dosen)
    REFERENCES dosen (id_dosen) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_kelas_ruangan FOREIGN KEY (id_ruangan)
    REFERENCES ruangan (id_ruangan) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. KRS = mahasiswa mengambil kelas (tabel asosiatif M:N)
-- ---------------------------------------------------------------------
CREATE TABLE krs (
  id_krs             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_mahasiswa       INT UNSIGNED NOT NULL,
  id_kelas           INT UNSIGNED NOT NULL,
  status             ENUM('Diajukan','Disetujui','Ditolak') NOT NULL DEFAULT 'Diajukan',
  tanggal_pengajuan  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_krs),
  UNIQUE KEY uq_krs (id_mahasiswa, id_kelas),
  KEY idx_krs_kelas (id_kelas),
  CONSTRAINT fk_krs_mhs FOREIGN KEY (id_mahasiswa)
    REFERENCES mahasiswa (id_mahasiswa) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_krs_kelas FOREIGN KEY (id_kelas)
    REFERENCES kelas (id_kelas) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 11. Nilai (relasi 1:1 dengan KRS). Hanya komponen nilai yang disimpan;
--     nilai akhir, huruf mutu, dan bobot dihitung di view v_nilai_akhir
--     supaya tidak ada atribut turunan (derived attribute) yang disimpan.
-- ---------------------------------------------------------------------
CREATE TABLE nilai (
  id_nilai        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  id_krs          INT UNSIGNED  NOT NULL,
  nilai_tugas     DECIMAL(5,2)  NULL,
  nilai_uts       DECIMAL(5,2)  NULL,
  nilai_uas       DECIMAL(5,2)  NULL,
  diperbarui_pada DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_nilai),
  UNIQUE KEY uq_nilai_krs (id_krs),
  CONSTRAINT chk_nilai_tugas CHECK (nilai_tugas BETWEEN 0 AND 100),
  CONSTRAINT chk_nilai_uts   CHECK (nilai_uts   BETWEEN 0 AND 100),
  CONSTRAINT chk_nilai_uas   CHECK (nilai_uas   BETWEEN 0 AND 100),
  CONSTRAINT fk_nilai_krs FOREIGN KEY (id_krs)
    REFERENCES krs (id_krs) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 12. Skala konversi nilai angka ke huruf mutu.
--     batas_bawah inklusif, batas_atas eksklusif.
-- ---------------------------------------------------------------------
CREATE TABLE skala_nilai (
  huruf        VARCHAR(2)   NOT NULL,
  batas_bawah  DECIMAL(5,2) NOT NULL,
  batas_atas   DECIMAL(5,2) NOT NULL,
  bobot        DECIMAL(3,2) NOT NULL,
  PRIMARY KEY (huruf)
) ENGINE=InnoDB;

-- =====================================================================
-- VIEW
-- =====================================================================

-- Nilai akhir = 30% tugas + 30% UTS + 40% UAS, lalu dikonversi ke huruf mutu.
CREATE VIEW v_nilai_akhir AS
SELECT
  n.id_nilai,
  n.id_krs,
  k.id_mahasiswa,
  k.id_kelas,
  kl.id_ta,
  mk.id_mk,
  mk.kode_mk,
  mk.nama_mk,
  mk.sks,
  n.nilai_tugas,
  n.nilai_uts,
  n.nilai_uas,
  ROUND(0.30 * n.nilai_tugas + 0.30 * n.nilai_uts + 0.40 * n.nilai_uas, 2) AS nilai_akhir,
  s.huruf,
  s.bobot
FROM nilai n
JOIN krs k          ON k.id_krs   = n.id_krs
JOIN kelas kl       ON kl.id_kelas = k.id_kelas
JOIN mata_kuliah mk ON mk.id_mk   = kl.id_mk
LEFT JOIN skala_nilai s
  ON ROUND(0.30 * n.nilai_tugas + 0.30 * n.nilai_uts + 0.40 * n.nilai_uas, 2) >= s.batas_bawah
 AND ROUND(0.30 * n.nilai_tugas + 0.30 * n.nilai_uts + 0.40 * n.nilai_uas, 2) <  s.batas_atas
WHERE k.status = 'Disetujui';

-- Indeks Prestasi Semester per mahasiswa per tahun akademik
CREATE VIEW v_ips AS
SELECT
  id_mahasiswa,
  id_ta,
  SUM(sks)                                  AS total_sks,
  ROUND(SUM(sks * bobot) / SUM(sks), 2)     AS ips
FROM v_nilai_akhir
WHERE huruf IS NOT NULL
GROUP BY id_mahasiswa, id_ta;

-- Indeks Prestasi Kumulatif per mahasiswa. Jika satu mata kuliah diulang,
-- hanya bobot terbaik yang dihitung (subquery per mahasiswa per mata kuliah).
CREATE VIEW v_ipk AS
SELECT
  id_mahasiswa,
  SUM(sks)                                  AS total_sks,
  ROUND(SUM(sks * bobot) / SUM(sks), 2)     AS ipk
FROM (
  SELECT id_mahasiswa, id_mk, MAX(sks) AS sks, MAX(bobot) AS bobot
  FROM v_nilai_akhir
  WHERE huruf IS NOT NULL
  GROUP BY id_mahasiswa, id_mk
) terbaik
GROUP BY id_mahasiswa;

-- =====================================================================
--  SIAKAD - data contoh (fiktif). Dibuat oleh tools/generate_seed.php
--  Akun demo: admin / admin123, dosen (NIDN) / dosen123, mahasiswa (NPM) / mhs123
-- =====================================================================

INSERT INTO fakultas (id_fakultas, kode_fakultas, nama_fakultas) VALUES
('1', 'FMIPA', 'Fakultas Matematika dan Ilmu Pengetahuan Alam'),
('2', 'FT', 'Fakultas Teknik');

INSERT INTO program_studi (id_prodi, kode_prodi, nama_prodi, jenjang, id_fakultas) VALUES
('1', 'INF', 'Informatika', 'S1', '1'),
('2', 'STA', 'Statistika', 'S1', '1'),
('3', 'MAT', 'Matematika', 'S1', '1'),
('4', 'TEL', 'Teknik Elektro', 'S1', '2'),
('5', 'TSP', 'Teknik Sipil', 'S1', '2');

INSERT INTO users (id_user, username, password_hash, role, is_aktif) VALUES
('1', 'admin', '$2y$10$F4M17NBVVunkAJk7n5znxuiyc1BZ86Nm8ZuRqjO8CvYcwc2Ey7wtq', 'admin', '1'),
('2', '0010018101', '$2y$10$w0V.Wj1rNgfyaVkfTuIHtueVhIMaIlrhfaGXFejtZ6QLIIeBiRifu', 'dosen', '1'),
('3', '0011028108', '$2y$10$w0V.Wj1rNgfyaVkfTuIHtueVhIMaIlrhfaGXFejtZ6QLIIeBiRifu', 'dosen', '1'),
('4', '0012038115', '$2y$10$w0V.Wj1rNgfyaVkfTuIHtueVhIMaIlrhfaGXFejtZ6QLIIeBiRifu', 'dosen', '1'),
('5', '0013048122', '$2y$10$w0V.Wj1rNgfyaVkfTuIHtueVhIMaIlrhfaGXFejtZ6QLIIeBiRifu', 'dosen', '1'),
('6', '0014058129', '$2y$10$w0V.Wj1rNgfyaVkfTuIHtueVhIMaIlrhfaGXFejtZ6QLIIeBiRifu', 'dosen', '1'),
('7', '0015068136', '$2y$10$w0V.Wj1rNgfyaVkfTuIHtueVhIMaIlrhfaGXFejtZ6QLIIeBiRifu', 'dosen', '1'),
('8', '0016078143', '$2y$10$w0V.Wj1rNgfyaVkfTuIHtueVhIMaIlrhfaGXFejtZ6QLIIeBiRifu', 'dosen', '1'),
('9', '2508107010001', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('10', '2508107010002', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('11', '2508107010003', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('12', '2508107010004', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('13', '2508107010005', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('14', '2508107010006', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('15', '2508107010007', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('16', '2508107010008', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('17', '2508107010009', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('18', '2508107010010', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('19', '2508107010011', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('20', '2508107010012', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('21', '2508107010013', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('22', '2508107010014', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('23', '2508107010015', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('24', '2508107010016', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('25', '2508108010001', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('26', '2508108010002', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('27', '2508108010003', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('28', '2508108010004', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('29', '2508108010005', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('30', '2508108010006', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('31', '2608107010001', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('32', '2608107010002', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('33', '2608107010003', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('34', '2608107010004', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('35', '2608107010005', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1'),
('36', '2608107010006', '$2y$10$RFS4HS2dwD2v6qIgTtgfVOMLonUID/X24CTq0iBQco8kdQ4rk1piC', 'mahasiswa', '1');

INSERT INTO dosen (id_dosen, nidn, nama_dosen, email, no_hp, id_prodi, id_user) VALUES
('1', '0010018101', 'Dr. Rahmat Hidayat, S.Kom., M.Kom.', 'rahmat.hidayat@dosen.siakad.test', '085252082305', '1', '2'),
('2', '0011028108', 'Nurul Fadhilah, S.Si., M.Sc.', 'nurul.fadhilah@dosen.siakad.test', '085262560774', '1', '3'),
('3', '0012038115', 'Ir. Teuku Iskandar, M.T.', 'teuku.iskandar@dosen.siakad.test', '085273871898', '1', '4'),
('4', '0013048122', 'Dr. Cut Meurah Intan, M.Si.', 'cut.meurah.intan@dosen.siakad.test', '085288273336', '2', '5'),
('5', '0014058129', 'Fajar Ramadhan, S.Stat., M.Stat.', 'fajar.ramadhan@dosen.siakad.test', '085264617421', '2', '6'),
('6', '0015068136', 'Dr. Zulfikar Amin, M.Kom.', 'zulfikar.amin@dosen.siakad.test', '085295491917', '1', '7'),
('7', '0016078143', 'Siti Rahmah, S.Pd., M.Si.', 'siti.rahmah@dosen.siakad.test', '085231818397', '3', '8');

INSERT INTO mahasiswa (id_mahasiswa, npm, nama_mahasiswa, jenis_kelamin, tanggal_lahir, email, angkatan, id_prodi, id_dosen_wali, id_user) VALUES
('1', '2508107010001', 'Rahmi Azzahra', 'P', '2006-06-25', '2508107010001@mhs.siakad.test', '2025', '1', '2', '9'),
('2', '2508107010002', 'Nadia Rahman', 'P', '2006-12-09', '2508107010002@mhs.siakad.test', '2025', '1', '1', '10'),
('3', '2508107010003', 'Rizki Maulana', 'L', '2006-12-13', '2508107010003@mhs.siakad.test', '2025', '1', '2', '11'),
('4', '2508107010004', 'Akbar Rahman', 'L', '2006-09-08', '2508107010004@mhs.siakad.test', '2025', '1', '1', '12'),
('5', '2508107010005', 'Putri Saputra', 'P', '2007-06-01', '2508107010005@mhs.siakad.test', '2025', '1', '2', '13'),
('6', '2508107010006', 'Rafi Akmal', 'L', '2007-06-08', '2508107010006@mhs.siakad.test', '2025', '1', '1', '14'),
('7', '2508107010007', 'Ilham Rahman', 'L', '2006-09-10', '2508107010007@mhs.siakad.test', '2025', '1', '2', '15'),
('8', '2508107010008', 'Syarifah Azzahra', 'P', '2007-02-18', '2508107010008@mhs.siakad.test', '2025', '1', '1', '16'),
('9', '2508107010009', 'Akbar Saputra', 'L', '2006-03-16', '2508107010009@mhs.siakad.test', '2025', '1', '2', '17'),
('10', '2508107010010', 'Intan Amalia', 'P', '2006-10-19', '2508107010010@mhs.siakad.test', '2025', '1', '1', '18'),
('11', '2508107010011', 'Mutia Fikri', 'P', '2007-03-09', '2508107010011@mhs.siakad.test', '2025', '1', '2', '19'),
('12', '2508107010012', 'Zikri Husna', 'L', '2006-01-24', '2508107010012@mhs.siakad.test', '2025', '1', '1', '20'),
('13', '2508107010013', 'Intan Maulana', 'P', '2006-11-01', '2508107010013@mhs.siakad.test', '2025', '1', '2', '21'),
('14', '2508107010014', 'Muhammad Maulana', 'L', '2007-10-27', '2508107010014@mhs.siakad.test', '2025', '1', '1', '22'),
('15', '2508107010015', 'Akbar Yusra', 'L', '2006-02-27', '2508107010015@mhs.siakad.test', '2025', '1', '2', '23'),
('16', '2508107010016', 'Zahra Maulana', 'P', '2006-12-05', '2508107010016@mhs.siakad.test', '2025', '1', '1', '24'),
('17', '2508108010001', 'Nadia Azzahra', 'P', '2006-03-06', '2508108010001@mhs.siakad.test', '2025', '2', '4', '25'),
('18', '2508108010002', 'Fadhil Fikri', 'L', '2007-04-09', '2508108010002@mhs.siakad.test', '2025', '2', '4', '26'),
('19', '2508108010003', 'Rafi Maulana', 'L', '2006-10-26', '2508108010003@mhs.siakad.test', '2025', '2', '4', '27'),
('20', '2508108010004', 'Rizki Akmal', 'L', '2006-07-18', '2508108010004@mhs.siakad.test', '2025', '2', '4', '28'),
('21', '2508108010005', 'Fitri Fikri', 'P', '2006-04-01', '2508108010005@mhs.siakad.test', '2025', '2', '4', '29'),
('22', '2508108010006', 'Rizki Yusra', 'L', '2006-01-03', '2508108010006@mhs.siakad.test', '2025', '2', '4', '30'),
('23', '2608107010001', 'Zahra Firdaus', 'P', '2008-05-07', '2608107010001@mhs.siakad.test', '2026', '1', '6', '31'),
('24', '2608107010002', 'Putri Maulana', 'P', '2008-04-13', '2608107010002@mhs.siakad.test', '2026', '1', '6', '32'),
('25', '2608107010003', 'Cut Rahman', 'P', '2007-06-26', '2608107010003@mhs.siakad.test', '2026', '1', '6', '33'),
('26', '2608107010004', 'Cut Safitri', 'P', '2007-06-09', '2608107010004@mhs.siakad.test', '2026', '1', '6', '34'),
('27', '2608107010005', 'Intan Rahman', 'P', '2008-11-10', '2608107010005@mhs.siakad.test', '2026', '1', '6', '35'),
('28', '2608107010006', 'Iqbal Safitri', 'L', '2007-12-21', '2608107010006@mhs.siakad.test', '2026', '1', '6', '36');

INSERT INTO mata_kuliah (id_mk, kode_mk, nama_mk, sks, semester_paket, jenis, id_prodi) VALUES
('1', 'INF101', 'Algoritma dan Pemrograman', '4', '1', 'Wajib', '1'),
('2', 'INF102', 'Kalkulus I', '3', '1', 'Wajib', '1'),
('3', 'INF103', 'Pengantar Teknologi Informasi', '2', '1', 'Wajib', '1'),
('4', 'INF104', 'Logika Informatika', '3', '1', 'Wajib', '1'),
('5', 'INF201', 'Struktur Data', '3', '2', 'Wajib', '1'),
('6', 'INF202', 'Matematika Diskrit', '3', '2', 'Wajib', '1'),
('7', 'INF203', 'Basis Data', '3', '2', 'Wajib', '1'),
('8', 'INF204', 'Pemrograman Berorientasi Objek', '3', '2', 'Wajib', '1'),
('9', 'INF301', 'Manajemen dan Pemodelan Data', '3', '3', 'Wajib', '1'),
('10', 'INF302', 'Pemrograman Web', '3', '3', 'Wajib', '1'),
('11', 'INF303', 'Sistem Operasi', '3', '3', 'Wajib', '1'),
('12', 'INF304', 'Jaringan Komputer', '3', '3', 'Wajib', '1'),
('13', 'INF305', 'Rekayasa Perangkat Lunak', '3', '3', 'Wajib', '1'),
('14', 'STA101', 'Pengantar Statistika', '3', '1', 'Wajib', '2'),
('15', 'STA102', 'Kalkulus Dasar', '3', '1', 'Wajib', '2'),
('16', 'STA103', 'Pengantar Komputasi Statistika', '2', '1', 'Wajib', '2'),
('17', 'STA201', 'Teori Peluang', '3', '2', 'Wajib', '2'),
('18', 'STA202', 'Aljabar Linear', '3', '2', 'Wajib', '2'),
('19', 'STA203', 'Metode Statistika', '3', '2', 'Wajib', '2'),
('20', 'STA301', 'Analisis Regresi', '3', '3', 'Wajib', '2'),
('21', 'STA302', 'Statistika Matematika', '3', '3', 'Wajib', '2'),
('22', 'STA303', 'Basis Data untuk Statistika', '3', '3', 'Pilihan', '2');

INSERT INTO tahun_akademik (id_ta, tahun, semester, is_aktif) VALUES
('1', '2025/2026', 'Ganjil', '0'),
('2', '2025/2026', 'Genap', '0'),
('3', '2026/2027', 'Ganjil', '1');

INSERT INTO ruangan (id_ruangan, kode_ruangan, nama_ruangan, gedung, kapasitas) VALUES
('1', 'MIPA-101', 'Ruang Kuliah 101', 'Gedung FMIPA Lt. 1', '40'),
('2', 'MIPA-102', 'Ruang Kuliah 102', 'Gedung FMIPA Lt. 1', '40'),
('3', 'MIPA-201', 'Ruang Kuliah 201', 'Gedung FMIPA Lt. 2', '50'),
('4', 'LAB-KOM1', 'Laboratorium Komputer 1', 'Gedung Informatika', '30'),
('5', 'LAB-KOM2', 'Laboratorium Komputer 2', 'Gedung Informatika', '30'),
('6', 'FT-A301', 'Ruang Kuliah A301', 'Gedung Teknik A', '60');

INSERT INTO skala_nilai (huruf, batas_bawah, batas_atas, bobot) VALUES
('A', '87', '101', '4'),
('AB', '78', '87', '3.5'),
('B', '69', '78', '3'),
('BC', '60', '69', '2.5'),
('C', '51', '60', '2'),
('D', '41', '51', '1'),
('E', '0', '41', '0');

INSERT INTO kelas (id_kelas, id_mk, id_ta, id_dosen, id_ruangan, nama_kelas, hari, jam_mulai, jam_selesai, kuota) VALUES
('1', '1', '1', '1', '4', '01', 'Senin', '10:00:00', '11:40:00', '40'),
('2', '1', '3', '1', '4', '01', 'Senin', '10:00:00', '11:40:00', '40'),
('3', '2', '1', '3', '2', '01', 'Selasa', '13:30:00', '15:10:00', '40'),
('4', '2', '3', '3', '2', '01', 'Selasa', '13:30:00', '15:10:00', '40'),
('5', '3', '1', '6', '1', '01', 'Rabu', '15:30:00', '17:10:00', '40'),
('6', '3', '3', '6', '1', '01', 'Rabu', '15:30:00', '17:10:00', '40'),
('7', '4', '1', '2', '2', '01', 'Kamis', '08:00:00', '09:40:00', '40'),
('8', '4', '3', '2', '2', '01', 'Kamis', '08:00:00', '09:40:00', '40'),
('9', '5', '2', '1', '1', '01', 'Senin', '10:00:00', '11:40:00', '40'),
('10', '6', '2', '3', '2', '01', 'Selasa', '13:30:00', '15:10:00', '40'),
('11', '7', '2', '2', '1', '01', 'Rabu', '15:30:00', '17:10:00', '40'),
('12', '8', '2', '6', '4', '01', 'Kamis', '08:00:00', '09:40:00', '40'),
('13', '9', '3', '2', '1', '01', 'Senin', '10:00:00', '11:40:00', '40'),
('14', '10', '3', '6', '4', '01', 'Selasa', '13:30:00', '15:10:00', '40'),
('15', '11', '3', '3', '1', '01', 'Rabu', '15:30:00', '17:10:00', '40'),
('16', '12', '3', '1', '2', '01', 'Kamis', '08:00:00', '09:40:00', '40'),
('17', '13', '3', '6', '1', '01', 'Jumat', '10:00:00', '11:40:00', '40'),
('18', '14', '1', '4', '3', '01', 'Senin', '13:30:00', '15:10:00', '40'),
('19', '15', '1', '7', '3', '01', 'Selasa', '15:30:00', '17:10:00', '40'),
('20', '16', '1', '5', '3', '01', 'Rabu', '08:00:00', '09:40:00', '40'),
('21', '17', '2', '4', '3', '01', 'Senin', '13:30:00', '15:10:00', '40'),
('22', '18', '2', '7', '3', '01', 'Selasa', '15:30:00', '17:10:00', '40'),
('23', '19', '2', '5', '3', '01', 'Rabu', '08:00:00', '09:40:00', '40'),
('24', '20', '3', '4', '3', '01', 'Senin', '13:30:00', '15:10:00', '40'),
('25', '21', '3', '5', '3', '01', 'Selasa', '15:30:00', '17:10:00', '40'),
('26', '22', '3', '2', '3', '01', 'Rabu', '08:00:00', '09:40:00', '40');

INSERT INTO krs (id_krs, id_mahasiswa, id_kelas, status, tanggal_pengajuan) VALUES
('1', '1', '1', 'Disetujui', '2025-08-25 09:00:00'),
('2', '1', '3', 'Disetujui', '2025-08-25 09:00:00'),
('3', '1', '5', 'Disetujui', '2025-08-25 09:00:00'),
('4', '1', '7', 'Disetujui', '2025-08-25 09:00:00'),
('5', '1', '9', 'Disetujui', '2026-02-09 09:00:00'),
('6', '1', '10', 'Disetujui', '2026-02-09 09:00:00'),
('7', '1', '11', 'Disetujui', '2026-02-09 09:00:00'),
('8', '1', '12', 'Disetujui', '2026-02-09 09:00:00'),
('9', '1', '13', 'Diajukan', '2026-08-24 09:00:00'),
('10', '1', '14', 'Diajukan', '2026-08-24 09:00:00'),
('11', '1', '15', 'Diajukan', '2026-08-24 09:00:00'),
('12', '1', '16', 'Diajukan', '2026-08-24 09:00:00'),
('13', '2', '1', 'Disetujui', '2025-08-25 09:00:00'),
('14', '2', '3', 'Disetujui', '2025-08-25 09:00:00'),
('15', '2', '5', 'Disetujui', '2025-08-25 09:00:00'),
('16', '2', '7', 'Disetujui', '2025-08-25 09:00:00'),
('17', '2', '9', 'Disetujui', '2026-02-09 09:00:00'),
('18', '2', '10', 'Disetujui', '2026-02-09 09:00:00'),
('19', '2', '11', 'Disetujui', '2026-02-09 09:00:00'),
('20', '2', '12', 'Disetujui', '2026-02-09 09:00:00'),
('21', '2', '13', 'Disetujui', '2026-08-24 09:00:00'),
('22', '2', '14', 'Disetujui', '2026-08-24 09:00:00'),
('23', '2', '15', 'Disetujui', '2026-08-24 09:00:00'),
('24', '2', '16', 'Disetujui', '2026-08-24 09:00:00'),
('25', '2', '17', 'Disetujui', '2026-08-24 09:00:00'),
('26', '3', '1', 'Disetujui', '2025-08-25 09:00:00'),
('27', '3', '3', 'Disetujui', '2025-08-25 09:00:00'),
('28', '3', '5', 'Disetujui', '2025-08-25 09:00:00'),
('29', '3', '7', 'Disetujui', '2025-08-25 09:00:00'),
('30', '3', '9', 'Disetujui', '2026-02-09 09:00:00'),
('31', '3', '10', 'Disetujui', '2026-02-09 09:00:00'),
('32', '3', '11', 'Disetujui', '2026-02-09 09:00:00'),
('33', '3', '12', 'Disetujui', '2026-02-09 09:00:00'),
('34', '3', '13', 'Disetujui', '2026-08-24 09:00:00'),
('35', '3', '14', 'Disetujui', '2026-08-24 09:00:00'),
('36', '3', '15', 'Disetujui', '2026-08-24 09:00:00'),
('37', '3', '16', 'Disetujui', '2026-08-24 09:00:00'),
('38', '3', '17', 'Disetujui', '2026-08-24 09:00:00'),
('39', '4', '1', 'Disetujui', '2025-08-25 09:00:00'),
('40', '4', '3', 'Disetujui', '2025-08-25 09:00:00'),
('41', '4', '5', 'Disetujui', '2025-08-25 09:00:00'),
('42', '4', '7', 'Disetujui', '2025-08-25 09:00:00'),
('43', '4', '9', 'Disetujui', '2026-02-09 09:00:00'),
('44', '4', '10', 'Disetujui', '2026-02-09 09:00:00'),
('45', '4', '11', 'Disetujui', '2026-02-09 09:00:00'),
('46', '4', '12', 'Disetujui', '2026-02-09 09:00:00'),
('47', '4', '13', 'Disetujui', '2026-08-24 09:00:00'),
('48', '4', '14', 'Disetujui', '2026-08-24 09:00:00'),
('49', '4', '15', 'Disetujui', '2026-08-24 09:00:00'),
('50', '4', '16', 'Disetujui', '2026-08-24 09:00:00'),
('51', '4', '17', 'Disetujui', '2026-08-24 09:00:00'),
('52', '5', '1', 'Disetujui', '2025-08-25 09:00:00'),
('53', '5', '3', 'Disetujui', '2025-08-25 09:00:00'),
('54', '5', '5', 'Disetujui', '2025-08-25 09:00:00'),
('55', '5', '7', 'Disetujui', '2025-08-25 09:00:00'),
('56', '5', '9', 'Disetujui', '2026-02-09 09:00:00'),
('57', '5', '10', 'Disetujui', '2026-02-09 09:00:00'),
('58', '5', '11', 'Disetujui', '2026-02-09 09:00:00'),
('59', '5', '12', 'Disetujui', '2026-02-09 09:00:00'),
('60', '5', '13', 'Disetujui', '2026-08-24 09:00:00'),
('61', '5', '14', 'Disetujui', '2026-08-24 09:00:00'),
('62', '5', '15', 'Disetujui', '2026-08-24 09:00:00'),
('63', '5', '16', 'Disetujui', '2026-08-24 09:00:00'),
('64', '5', '17', 'Disetujui', '2026-08-24 09:00:00'),
('65', '6', '1', 'Disetujui', '2025-08-25 09:00:00'),
('66', '6', '3', 'Disetujui', '2025-08-25 09:00:00'),
('67', '6', '5', 'Disetujui', '2025-08-25 09:00:00'),
('68', '6', '7', 'Disetujui', '2025-08-25 09:00:00'),
('69', '6', '9', 'Disetujui', '2026-02-09 09:00:00'),
('70', '6', '10', 'Disetujui', '2026-02-09 09:00:00'),
('71', '6', '11', 'Disetujui', '2026-02-09 09:00:00'),
('72', '6', '12', 'Disetujui', '2026-02-09 09:00:00'),
('73', '6', '13', 'Disetujui', '2026-08-24 09:00:00'),
('74', '6', '14', 'Disetujui', '2026-08-24 09:00:00'),
('75', '6', '15', 'Disetujui', '2026-08-24 09:00:00'),
('76', '6', '16', 'Disetujui', '2026-08-24 09:00:00'),
('77', '6', '17', 'Disetujui', '2026-08-24 09:00:00'),
('78', '7', '1', 'Disetujui', '2025-08-25 09:00:00'),
('79', '7', '3', 'Disetujui', '2025-08-25 09:00:00'),
('80', '7', '5', 'Disetujui', '2025-08-25 09:00:00'),
('81', '7', '7', 'Disetujui', '2025-08-25 09:00:00'),
('82', '7', '9', 'Disetujui', '2026-02-09 09:00:00'),
('83', '7', '10', 'Disetujui', '2026-02-09 09:00:00'),
('84', '7', '11', 'Disetujui', '2026-02-09 09:00:00'),
('85', '7', '12', 'Disetujui', '2026-02-09 09:00:00'),
('86', '7', '13', 'Disetujui', '2026-08-24 09:00:00'),
('87', '7', '14', 'Disetujui', '2026-08-24 09:00:00'),
('88', '7', '15', 'Disetujui', '2026-08-24 09:00:00'),
('89', '7', '16', 'Disetujui', '2026-08-24 09:00:00'),
('90', '7', '17', 'Disetujui', '2026-08-24 09:00:00'),
('91', '8', '1', 'Disetujui', '2025-08-25 09:00:00'),
('92', '8', '3', 'Disetujui', '2025-08-25 09:00:00'),
('93', '8', '5', 'Disetujui', '2025-08-25 09:00:00'),
('94', '8', '7', 'Disetujui', '2025-08-25 09:00:00'),
('95', '8', '9', 'Disetujui', '2026-02-09 09:00:00'),
('96', '8', '10', 'Disetujui', '2026-02-09 09:00:00'),
('97', '8', '11', 'Disetujui', '2026-02-09 09:00:00'),
('98', '8', '12', 'Disetujui', '2026-02-09 09:00:00'),
('99', '8', '13', 'Disetujui', '2026-08-24 09:00:00'),
('100', '8', '14', 'Disetujui', '2026-08-24 09:00:00'),
('101', '8', '15', 'Disetujui', '2026-08-24 09:00:00'),
('102', '8', '16', 'Disetujui', '2026-08-24 09:00:00'),
('103', '8', '17', 'Disetujui', '2026-08-24 09:00:00'),
('104', '9', '1', 'Disetujui', '2025-08-25 09:00:00'),
('105', '9', '3', 'Disetujui', '2025-08-25 09:00:00'),
('106', '9', '5', 'Disetujui', '2025-08-25 09:00:00'),
('107', '9', '7', 'Disetujui', '2025-08-25 09:00:00'),
('108', '9', '9', 'Disetujui', '2026-02-09 09:00:00'),
('109', '9', '10', 'Disetujui', '2026-02-09 09:00:00'),
('110', '9', '11', 'Disetujui', '2026-02-09 09:00:00'),
('111', '9', '12', 'Disetujui', '2026-02-09 09:00:00'),
('112', '9', '13', 'Disetujui', '2026-08-24 09:00:00'),
('113', '9', '14', 'Disetujui', '2026-08-24 09:00:00'),
('114', '9', '15', 'Disetujui', '2026-08-24 09:00:00'),
('115', '9', '16', 'Disetujui', '2026-08-24 09:00:00'),
('116', '9', '17', 'Disetujui', '2026-08-24 09:00:00'),
('117', '10', '1', 'Disetujui', '2025-08-25 09:00:00'),
('118', '10', '3', 'Disetujui', '2025-08-25 09:00:00'),
('119', '10', '5', 'Disetujui', '2025-08-25 09:00:00'),
('120', '10', '7', 'Disetujui', '2025-08-25 09:00:00'),
('121', '10', '9', 'Disetujui', '2026-02-09 09:00:00'),
('122', '10', '10', 'Disetujui', '2026-02-09 09:00:00'),
('123', '10', '11', 'Disetujui', '2026-02-09 09:00:00'),
('124', '10', '12', 'Disetujui', '2026-02-09 09:00:00'),
('125', '10', '13', 'Disetujui', '2026-08-24 09:00:00'),
('126', '10', '14', 'Disetujui', '2026-08-24 09:00:00'),
('127', '10', '15', 'Disetujui', '2026-08-24 09:00:00'),
('128', '10', '16', 'Disetujui', '2026-08-24 09:00:00'),
('129', '10', '17', 'Disetujui', '2026-08-24 09:00:00'),
('130', '11', '1', 'Disetujui', '2025-08-25 09:00:00'),
('131', '11', '3', 'Disetujui', '2025-08-25 09:00:00'),
('132', '11', '5', 'Disetujui', '2025-08-25 09:00:00'),
('133', '11', '7', 'Disetujui', '2025-08-25 09:00:00'),
('134', '11', '9', 'Disetujui', '2026-02-09 09:00:00'),
('135', '11', '10', 'Disetujui', '2026-02-09 09:00:00'),
('136', '11', '11', 'Disetujui', '2026-02-09 09:00:00'),
('137', '11', '12', 'Disetujui', '2026-02-09 09:00:00'),
('138', '11', '13', 'Disetujui', '2026-08-24 09:00:00'),
('139', '11', '14', 'Disetujui', '2026-08-24 09:00:00'),
('140', '11', '15', 'Disetujui', '2026-08-24 09:00:00'),
('141', '11', '16', 'Disetujui', '2026-08-24 09:00:00'),
('142', '11', '17', 'Disetujui', '2026-08-24 09:00:00'),
('143', '12', '1', 'Disetujui', '2025-08-25 09:00:00'),
('144', '12', '3', 'Disetujui', '2025-08-25 09:00:00'),
('145', '12', '5', 'Disetujui', '2025-08-25 09:00:00'),
('146', '12', '7', 'Disetujui', '2025-08-25 09:00:00'),
('147', '12', '9', 'Disetujui', '2026-02-09 09:00:00'),
('148', '12', '10', 'Disetujui', '2026-02-09 09:00:00'),
('149', '12', '11', 'Disetujui', '2026-02-09 09:00:00'),
('150', '12', '12', 'Disetujui', '2026-02-09 09:00:00'),
('151', '12', '13', 'Disetujui', '2026-08-24 09:00:00'),
('152', '12', '14', 'Disetujui', '2026-08-24 09:00:00'),
('153', '12', '15', 'Disetujui', '2026-08-24 09:00:00'),
('154', '12', '16', 'Disetujui', '2026-08-24 09:00:00'),
('155', '12', '17', 'Disetujui', '2026-08-24 09:00:00'),
('156', '13', '1', 'Disetujui', '2025-08-25 09:00:00'),
('157', '13', '3', 'Disetujui', '2025-08-25 09:00:00'),
('158', '13', '5', 'Disetujui', '2025-08-25 09:00:00'),
('159', '13', '7', 'Disetujui', '2025-08-25 09:00:00'),
('160', '13', '9', 'Disetujui', '2026-02-09 09:00:00'),
('161', '13', '10', 'Disetujui', '2026-02-09 09:00:00'),
('162', '13', '11', 'Disetujui', '2026-02-09 09:00:00'),
('163', '13', '12', 'Disetujui', '2026-02-09 09:00:00'),
('164', '13', '13', 'Disetujui', '2026-08-24 09:00:00'),
('165', '13', '14', 'Disetujui', '2026-08-24 09:00:00'),
('166', '13', '15', 'Disetujui', '2026-08-24 09:00:00'),
('167', '13', '16', 'Disetujui', '2026-08-24 09:00:00'),
('168', '13', '17', 'Disetujui', '2026-08-24 09:00:00'),
('169', '14', '1', 'Disetujui', '2025-08-25 09:00:00'),
('170', '14', '3', 'Disetujui', '2025-08-25 09:00:00'),
('171', '14', '5', 'Disetujui', '2025-08-25 09:00:00'),
('172', '14', '7', 'Disetujui', '2025-08-25 09:00:00'),
('173', '14', '9', 'Disetujui', '2026-02-09 09:00:00'),
('174', '14', '10', 'Disetujui', '2026-02-09 09:00:00'),
('175', '14', '11', 'Disetujui', '2026-02-09 09:00:00'),
('176', '14', '12', 'Disetujui', '2026-02-09 09:00:00'),
('177', '14', '13', 'Disetujui', '2026-08-24 09:00:00'),
('178', '14', '14', 'Disetujui', '2026-08-24 09:00:00'),
('179', '14', '15', 'Disetujui', '2026-08-24 09:00:00'),
('180', '14', '16', 'Disetujui', '2026-08-24 09:00:00'),
('181', '14', '17', 'Disetujui', '2026-08-24 09:00:00'),
('182', '15', '1', 'Disetujui', '2025-08-25 09:00:00'),
('183', '15', '3', 'Disetujui', '2025-08-25 09:00:00'),
('184', '15', '5', 'Disetujui', '2025-08-25 09:00:00'),
('185', '15', '7', 'Disetujui', '2025-08-25 09:00:00'),
('186', '15', '9', 'Disetujui', '2026-02-09 09:00:00'),
('187', '15', '10', 'Disetujui', '2026-02-09 09:00:00'),
('188', '15', '11', 'Disetujui', '2026-02-09 09:00:00'),
('189', '15', '12', 'Disetujui', '2026-02-09 09:00:00'),
('190', '15', '13', 'Disetujui', '2026-08-24 09:00:00'),
('191', '15', '14', 'Disetujui', '2026-08-24 09:00:00'),
('192', '15', '15', 'Disetujui', '2026-08-24 09:00:00'),
('193', '15', '16', 'Disetujui', '2026-08-24 09:00:00'),
('194', '15', '17', 'Disetujui', '2026-08-24 09:00:00'),
('195', '16', '1', 'Disetujui', '2025-08-25 09:00:00'),
('196', '16', '3', 'Disetujui', '2025-08-25 09:00:00'),
('197', '16', '5', 'Disetujui', '2025-08-25 09:00:00'),
('198', '16', '7', 'Disetujui', '2025-08-25 09:00:00'),
('199', '16', '9', 'Disetujui', '2026-02-09 09:00:00'),
('200', '16', '10', 'Disetujui', '2026-02-09 09:00:00'),
('201', '16', '11', 'Disetujui', '2026-02-09 09:00:00'),
('202', '16', '12', 'Disetujui', '2026-02-09 09:00:00'),
('203', '16', '13', 'Disetujui', '2026-08-24 09:00:00'),
('204', '16', '14', 'Disetujui', '2026-08-24 09:00:00'),
('205', '16', '15', 'Disetujui', '2026-08-24 09:00:00'),
('206', '16', '16', 'Disetujui', '2026-08-24 09:00:00'),
('207', '16', '17', 'Disetujui', '2026-08-24 09:00:00'),
('208', '17', '18', 'Disetujui', '2025-08-25 09:00:00'),
('209', '17', '19', 'Disetujui', '2025-08-25 09:00:00'),
('210', '17', '20', 'Disetujui', '2025-08-25 09:00:00'),
('211', '17', '21', 'Disetujui', '2026-02-09 09:00:00'),
('212', '17', '22', 'Disetujui', '2026-02-09 09:00:00'),
('213', '17', '23', 'Disetujui', '2026-02-09 09:00:00'),
('214', '17', '24', 'Disetujui', '2026-08-24 09:00:00'),
('215', '17', '25', 'Disetujui', '2026-08-24 09:00:00'),
('216', '17', '26', 'Disetujui', '2026-08-24 09:00:00'),
('217', '18', '18', 'Disetujui', '2025-08-25 09:00:00'),
('218', '18', '19', 'Disetujui', '2025-08-25 09:00:00'),
('219', '18', '20', 'Disetujui', '2025-08-25 09:00:00'),
('220', '18', '21', 'Disetujui', '2026-02-09 09:00:00'),
('221', '18', '22', 'Disetujui', '2026-02-09 09:00:00'),
('222', '18', '23', 'Disetujui', '2026-02-09 09:00:00'),
('223', '18', '24', 'Disetujui', '2026-08-24 09:00:00'),
('224', '18', '25', 'Disetujui', '2026-08-24 09:00:00'),
('225', '18', '26', 'Disetujui', '2026-08-24 09:00:00'),
('226', '19', '18', 'Disetujui', '2025-08-25 09:00:00'),
('227', '19', '19', 'Disetujui', '2025-08-25 09:00:00'),
('228', '19', '20', 'Disetujui', '2025-08-25 09:00:00'),
('229', '19', '21', 'Disetujui', '2026-02-09 09:00:00'),
('230', '19', '22', 'Disetujui', '2026-02-09 09:00:00'),
('231', '19', '23', 'Disetujui', '2026-02-09 09:00:00'),
('232', '19', '24', 'Disetujui', '2026-08-24 09:00:00'),
('233', '19', '25', 'Disetujui', '2026-08-24 09:00:00'),
('234', '19', '26', 'Disetujui', '2026-08-24 09:00:00'),
('235', '20', '18', 'Disetujui', '2025-08-25 09:00:00'),
('236', '20', '19', 'Disetujui', '2025-08-25 09:00:00'),
('237', '20', '20', 'Disetujui', '2025-08-25 09:00:00'),
('238', '20', '21', 'Disetujui', '2026-02-09 09:00:00'),
('239', '20', '22', 'Disetujui', '2026-02-09 09:00:00'),
('240', '20', '23', 'Disetujui', '2026-02-09 09:00:00'),
('241', '20', '24', 'Disetujui', '2026-08-24 09:00:00'),
('242', '20', '25', 'Disetujui', '2026-08-24 09:00:00'),
('243', '20', '26', 'Disetujui', '2026-08-24 09:00:00'),
('244', '21', '18', 'Disetujui', '2025-08-25 09:00:00'),
('245', '21', '19', 'Disetujui', '2025-08-25 09:00:00'),
('246', '21', '20', 'Disetujui', '2025-08-25 09:00:00'),
('247', '21', '21', 'Disetujui', '2026-02-09 09:00:00'),
('248', '21', '22', 'Disetujui', '2026-02-09 09:00:00'),
('249', '21', '23', 'Disetujui', '2026-02-09 09:00:00'),
('250', '21', '24', 'Disetujui', '2026-08-24 09:00:00'),
('251', '21', '25', 'Disetujui', '2026-08-24 09:00:00'),
('252', '21', '26', 'Disetujui', '2026-08-24 09:00:00'),
('253', '22', '18', 'Disetujui', '2025-08-25 09:00:00'),
('254', '22', '19', 'Disetujui', '2025-08-25 09:00:00'),
('255', '22', '20', 'Disetujui', '2025-08-25 09:00:00'),
('256', '22', '21', 'Disetujui', '2026-02-09 09:00:00'),
('257', '22', '22', 'Disetujui', '2026-02-09 09:00:00'),
('258', '22', '23', 'Disetujui', '2026-02-09 09:00:00'),
('259', '22', '24', 'Disetujui', '2026-08-24 09:00:00'),
('260', '22', '25', 'Disetujui', '2026-08-24 09:00:00'),
('261', '22', '26', 'Disetujui', '2026-08-24 09:00:00'),
('262', '23', '2', 'Diajukan', '2026-08-24 09:00:00'),
('263', '23', '4', 'Diajukan', '2026-08-24 09:00:00'),
('264', '23', '6', 'Diajukan', '2026-08-24 09:00:00'),
('265', '23', '8', 'Diajukan', '2026-08-24 09:00:00'),
('266', '25', '2', 'Diajukan', '2026-08-24 09:00:00'),
('267', '25', '4', 'Diajukan', '2026-08-24 09:00:00'),
('268', '25', '6', 'Diajukan', '2026-08-24 09:00:00'),
('269', '25', '8', 'Diajukan', '2026-08-24 09:00:00'),
('270', '27', '2', 'Diajukan', '2026-08-24 09:00:00'),
('271', '27', '4', 'Diajukan', '2026-08-24 09:00:00'),
('272', '27', '6', 'Diajukan', '2026-08-24 09:00:00'),
('273', '27', '8', 'Diajukan', '2026-08-24 09:00:00');

INSERT INTO nilai (id_krs, nilai_tugas, nilai_uts, nilai_uas) VALUES
('1', '67', '73', '76'),
('2', '77', '60', '68'),
('3', '72', '60', '67'),
('4', '80', '75', '72'),
('5', '78', '67', '61'),
('6', '76', '69', '74'),
('7', '67', '63', '76'),
('8', '68', '80', '71'),
('13', '88', '79', '78'),
('14', '90', '77', '72'),
('15', '88', '78', '71'),
('16', '78', '77', '69'),
('17', '84', '66', '68'),
('18', '84', '77', '81'),
('19', '75', '76', '72'),
('20', '79', '77', '76'),
('21', '84', NULL, NULL),
('26', '98', '75', '91'),
('27', '85', '90', '89'),
('28', '95', '95', '84'),
('29', '87', '96', '80'),
('30', '96', '96', '79'),
('31', '94', '82', '89'),
('32', '90', '94', '87'),
('33', '97', '97', '97'),
('34', '85', NULL, NULL),
('39', '71', '49', '58'),
('40', '73', '66', '68'),
('41', '67', '67', '62'),
('42', '58', '54', '57'),
('43', '71', '49', '65'),
('44', '58', '51', '61'),
('45', '71', '56', '52'),
('46', '65', '68', '53'),
('47', '70', NULL, NULL),
('52', '91', '81', '80'),
('53', '91', '93', '87'),
('54', '90', '88', '78'),
('55', '89', '83', '82'),
('56', '87', '96', '90'),
('57', '98', '92', '85'),
('58', '93', '86', '77'),
('59', '97', '87', '81'),
('60', '96', NULL, NULL),
('65', '100', '83', '85'),
('66', '88', '95', '83'),
('67', '98', '86', '87'),
('68', '98', '89', '95'),
('69', '95', '86', '91'),
('70', '87', '89', '87'),
('71', '100', '98', '96'),
('72', '94', '76', '89'),
('73', '94', NULL, NULL),
('78', '89', '95', '90'),
('79', '92', '77', '92'),
('80', '87', '94', '94'),
('81', '88', '80', '83'),
('82', '83', '94', '88'),
('83', '93', '76', '82'),
('84', '93', '95', '79'),
('85', '90', '87', '79'),
('86', '95', NULL, NULL),
('91', '72', '66', '68'),
('92', '78', '59', '60'),
('93', '67', '63', '69'),
('94', '81', '65', '63'),
('95', '82', '73', '75'),
('96', '75', '67', '74'),
('97', '77', '61', '73'),
('98', '75', '68', '68'),
('99', '73', NULL, NULL),
('104', '99', '91', '95'),
('105', '99', '84', '85'),
('106', '96', '77', '86'),
('107', '98', '80', '82'),
('108', '95', '84', '94'),
('109', '90', '85', '78'),
('110', '91', '91', '98'),
('111', '90', '77', '93'),
('112', '89', NULL, NULL),
('117', '84', '61', '75'),
('118', '84', '82', '69'),
('119', '80', '73', '80'),
('120', '78', '62', '68'),
('121', '80', '77', '80'),
('122', '83', '78', '77'),
('123', '78', '79', '70'),
('124', '76', '73', '73'),
('125', '78', NULL, NULL),
('130', '85', '75', '77'),
('131', '74', '76', '74'),
('132', '81', '69', '87'),
('133', '83', '64', '73'),
('134', '84', '85', '69'),
('135', '84', '67', '83'),
('136', '74', '80', '84'),
('137', '77', '80', '87'),
('138', '80', NULL, NULL),
('143', '69', '56', '67'),
('144', '70', '61', '69'),
('145', '70', '55', '64'),
('146', '75', '74', '77'),
('147', '80', '63', '71'),
('148', '65', '67', '68'),
('149', '71', '67', '63'),
('150', '69', '75', '62'),
('151', '68', NULL, NULL),
('156', '60', '61', '54'),
('157', '60', '63', '55'),
('158', '55', '65', '57'),
('159', '61', '44', '48'),
('160', '66', '43', '50'),
('161', '56', '65', '59'),
('162', '62', '52', '57'),
('163', '67', '58', '62'),
('164', '59', NULL, NULL),
('169', '95', '82', '81'),
('170', '93', '92', '86'),
('171', '82', '78', '74'),
('172', '90', '76', '89'),
('173', '83', '91', '90'),
('174', '83', '92', '87'),
('175', '83', '84', '85'),
('176', '92', '73', '85'),
('177', '82', NULL, NULL),
('182', '96', '79', '92'),
('183', '90', '85', '94'),
('184', '84', '85', '87'),
('185', '89', '73', '91'),
('186', '87', '89', '90'),
('187', '81', '88', '85'),
('188', '85', '84', '82'),
('189', '85', '78', '89'),
('190', '81', NULL, NULL),
('195', '82', '70', '78'),
('196', '87', '80', '72'),
('197', '89', '84', '78'),
('198', '81', '69', '78'),
('199', '84', '76', '74'),
('200', '79', '69', '90'),
('201', '80', '71', '76'),
('202', '77', '78', '71'),
('203', '89', NULL, NULL),
('208', '90', '78', '97'),
('209', '86', '83', '98'),
('210', '95', '88', '88'),
('211', '100', '75', '95'),
('212', '100', '89', '84'),
('213', '92', '83', '78'),
('214', '96', NULL, NULL),
('217', '64', '55', '62'),
('218', '68', '64', '68'),
('219', '71', '58', '55'),
('220', '63', '66', '53'),
('221', '60', '48', '62'),
('222', '62', '51', '59'),
('223', '63', NULL, NULL),
('226', '70', '66', '79'),
('227', '74', '78', '72'),
('228', '75', '82', '83'),
('229', '84', '65', '76'),
('230', '77', '68', '67'),
('231', '71', '79', '72'),
('232', '81', NULL, NULL),
('235', '95', '78', '93'),
('236', '90', '85', '80'),
('237', '86', '95', '78'),
('238', '96', '74', '83'),
('239', '85', '77', '87'),
('240', '82', '94', '79'),
('241', '88', NULL, NULL),
('244', '74', '84', '64'),
('245', '80', '84', '75'),
('246', '75', '76', '72'),
('247', '72', '79', '79'),
('248', '79', '75', '71'),
('249', '82', '71', '67'),
('250', '75', NULL, NULL),
('253', '82', '66', '71'),
('254', '80', '61', '70'),
('255', '80', '68', '75'),
('256', '84', '79', '83'),
('257', '71', '73', '74'),
('258', '81', '70', '63'),
('259', '73', NULL, NULL);
