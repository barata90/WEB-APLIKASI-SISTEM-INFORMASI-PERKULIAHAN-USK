-- =====================================================================
--  SIAKAD - Sistem Informasi Perkuliahan
--  Skema basis data (DDL) untuk MySQL 8 / MariaDB 10.4+
--  Semua tabel memakai InnoDB agar foreign key dan transaksi berlaku.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_siakad
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_siakad;

SET FOREIGN_KEY_CHECKS = 0;
DROP VIEW  IF EXISTS v_ipk;
DROP VIEW  IF EXISTS v_ips;
DROP VIEW  IF EXISTS v_nilai_akhir;
DROP TABLE IF EXISTS nilai;
DROP TABLE IF EXISTS krs;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS skala_nilai;
DROP TABLE IF EXISTS ruangan;
DROP TABLE IF EXISTS tahun_akademik;
DROP TABLE IF EXISTS mata_kuliah;
DROP TABLE IF EXISTS mahasiswa;
DROP TABLE IF EXISTS dosen;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS program_studi;
DROP TABLE IF EXISTS fakultas;
SET FOREIGN_KEY_CHECKS = 1;

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
