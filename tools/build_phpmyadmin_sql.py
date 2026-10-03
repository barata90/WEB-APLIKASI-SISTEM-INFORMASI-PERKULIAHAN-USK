#!/usr/bin/env python3
"""
Membuat database/siakad_phpmyadmin.sql dari schema.sql + seed.sql untuk diimpor
lewat phpMyAdmin di server bersama (misalnya dba.dotdigital.id).

Perbedaan dengan schema.sql/seed.sql:
- tanpa CREATE DATABASE dan USE: basis data dipilih di phpMyAdmin;
- tanpa DROP TABLE, DROP VIEW, TRUNCATE, dan DELETE: tabel lain di akun yang sama tidak
  akan terhapus. Jika nama tabel sudah ada, impor berhenti dengan galat
  "Table already exists" dan tidak ada data yang hilang.

Pemakaian:  python3 tools/build_phpmyadmin_sql.py
"""
import re
from pathlib import Path

root = Path(__file__).resolve().parent.parent
schema = (root / 'database' / 'schema.sql').read_text(encoding='utf-8')
seed = (root / 'database' / 'seed.sql').read_text(encoding='utf-8')

def clean(sql: str) -> str:
    sql = re.sub(r'CREATE DATABASE IF NOT EXISTS[^;]*;\s*', '', sql)
    sql = re.sub(r'^\s*USE\s+\w+;\s*$', '', sql, flags=re.M)
    sql = re.sub(r'^\s*(DROP\s+(TABLE|VIEW)\s+IF\s+EXISTS|TRUNCATE\s+TABLE|DELETE\s+FROM)[^;]*;\s*$', '', sql, flags=re.M)
    sql = re.sub(r'^\s*SET FOREIGN_KEY_CHECKS = [01];\s*$', '', sql, flags=re.M)
    return re.sub(r'\n{3,}', '\n\n', sql).strip() + '\n'

header = """-- =====================================================================
--  SIAKAD - berkas impor phpMyAdmin (schema + data contoh fiktif)
--  1. Buat basis data BARU yang kosong, mis. siakad_uts (utf8mb4_unicode_ci).
--     Jangan impor ke basis data tugas lain: nama tabelnya bisa sama.
--  2. Pilih basis data tersebut, buka tab Import, pilih berkas ini, klik Import.
--  Akun demo: admin / admin123, dosen 0011028108 / dosen123,
--             mahasiswa 2508107010001 / mhs123
--  Dibuat oleh tools/build_phpmyadmin_sql.py; jangan diedit manual.
-- =====================================================================
SET NAMES utf8mb4;

"""
out = root / 'database' / 'siakad_phpmyadmin.sql'
out.write_text(header + clean(schema) + '\n' + clean(seed), encoding='utf-8')
print('ditulis:', out.relative_to(root))
