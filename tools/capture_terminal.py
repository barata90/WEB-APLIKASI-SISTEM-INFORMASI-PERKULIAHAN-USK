#!/usr/bin/env python3
"""
Menjalankan perintah server/basis data yang sebenarnya, menyimpan keluarannya
sebagai teks (docs/terminal/*.txt) dan halaman HTML bergaya terminal yang
kemudian di-screenshot oleh tools/screenshot_terminal.js.

Pemakaian:  python3 tools/capture_terminal.py
"""
import html
import os
import subprocess

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
TXT_DIR = os.path.join(ROOT, "docs", "terminal")
HTML_DIR = os.path.join(TXT_DIR, "html")
os.makedirs(HTML_DIR, exist_ok=True)

SQL = "mariadb -t db_siakad -e"

GROUPS = [
    ("T01-spesifikasi-hardware", "Spesifikasi perangkat keras server", [
        "lscpu | grep -E 'Architecture|Model name|^CPU\\(s\\)|Thread|Core|Socket|L3'",
        "free -h",
        "df -h /",
    ]),
    ("T02-spesifikasi-software", "Sistem operasi dan perangkat lunak server", [
        "grep -E '^(PRETTY_NAME|VERSION)=' /etc/os-release",
        "uname -srm",
        "apache2 -v",
        "php -v | head -1",
        "mariadb --version",
        "apache2ctl -M 2>/dev/null | grep -E 'php|rewrite|alias|dir_module'",
    ]),
    ("T03-konfigurasi-apache", "Konfigurasi Apache untuk SIAKAD", [
        "cat /etc/apache2/conf-available/siakad.conf",
        "ls -l /var/www/html/ | grep siakad",
        "apache2ctl configtest",
        "apache2ctl -S 2>&1 | head -8",
    ]),
    ("T04-htaccess", "Berkas .htaccess aplikasi", [
        "cat .htaccess",
    ]),
    ("T05-layanan-port", "Status layanan dan port yang terbuka", [
        "service apache2 status",
        "service mariadb status | grep -E 'Uptime|Threads|Server version'",
        r"""ss -tlnp | grep -E ':80 |:3306 ' | awk '{split($6, a, "\""); print $1, $4, a[2]}'""",
    ]),
    ("T06-uji-http", "Uji koneksi HTTP dari klien ke server", [
        "curl -sI http://localhost/siakad/ | head -8",
        "curl -s -o /dev/null -w 'GET /siakad/config/config.php -> HTTP %{http_code}\\n' http://localhost/siakad/config/config.php",
        "curl -s -o /dev/null -w 'GET /siakad/database/schema.sql -> HTTP %{http_code}\\n' http://localhost/siakad/database/schema.sql",
    ]),
    ("T07-struktur-folder", "Struktur folder proyek", [
        "tree -L 3 --dirsfirst -I 'screenshots|html|terminal|diagram|node_modules' --noreport",
    ]),
    ("T08-daftar-tabel", "Daftar tabel dan view basis data db_siakad", [
        f"{SQL} \"SELECT TABLE_NAME AS objek, TABLE_TYPE AS jenis, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_siakad' ORDER BY TABLE_TYPE, TABLE_NAME\"",
    ]),
    ("T09-describe-mahasiswa", "Struktur tabel mahasiswa", [
        f"{SQL} 'DESCRIBE mahasiswa'",
    ]),
    ("T10-describe-krs-nilai", "Struktur tabel krs dan nilai", [
        f"{SQL} 'DESCRIBE krs; DESCRIBE nilai'",
    ]),
    ("T11-query-join", "SELECT dengan JOIN: mahasiswa, prodi, dosen wali, IPK", [
        f"{SQL} \"SELECT m.npm, m.nama_mahasiswa, p.nama_prodi, d.nama_dosen AS dosen_wali, i.ipk FROM mahasiswa m JOIN program_studi p ON p.id_prodi = m.id_prodi LEFT JOIN dosen d ON d.id_dosen = m.id_dosen_wali LEFT JOIN v_ipk i ON i.id_mahasiswa = m.id_mahasiswa WHERE m.angkatan = 2025 ORDER BY i.ipk DESC LIMIT 8\"",
    ]),
    ("T12-query-khs", "Query KHS (5 tabel + view) untuk satu mahasiswa", [
        f"{SQL} \"SELECT mk.kode_mk, mk.nama_mk, mk.sks, v.nilai_tugas AS tugas, v.nilai_uts AS uts, v.nilai_uas AS uas, v.nilai_akhir, v.huruf, v.bobot FROM krs k JOIN kelas kl ON kl.id_kelas = k.id_kelas JOIN mata_kuliah mk ON mk.id_mk = kl.id_mk LEFT JOIN v_nilai_akhir v ON v.id_krs = k.id_krs WHERE k.id_mahasiswa = 1 AND kl.id_ta = 2 ORDER BY mk.kode_mk\"",
        f"{SQL} \"SELECT * FROM v_ips WHERE id_mahasiswa = 1; SELECT * FROM v_ipk WHERE id_mahasiswa = 1\"",
    ]),
    ("T13-query-agregat", "Query agregat: GROUP BY dan HAVING", [
        f"{SQL} \"SELECT p.nama_prodi, COUNT(m.id_mahasiswa) AS jumlah_mhs, ROUND(AVG(i.ipk),2) AS rata_ipk FROM program_studi p LEFT JOIN mahasiswa m ON m.id_prodi = p.id_prodi LEFT JOIN v_ipk i ON i.id_mahasiswa = m.id_mahasiswa GROUP BY p.id_prodi HAVING jumlah_mhs > 0\"",
        f"{SQL} \"SELECT mk.nama_mk, d.nama_dosen, COUNT(k.id_krs) AS peserta FROM kelas kl JOIN mata_kuliah mk ON mk.id_mk = kl.id_mk JOIN dosen d ON d.id_dosen = kl.id_dosen LEFT JOIN krs k ON k.id_kelas = kl.id_kelas WHERE kl.id_ta = 3 GROUP BY kl.id_kelas ORDER BY peserta DESC LIMIT 6\"",
    ]),
    ("T14-insert-update-delete", "INSERT, UPDATE, DELETE dalam transaksi (di-ROLLBACK)", [
        f"{SQL} \"START TRANSACTION; INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester_paket, jenis, id_prodi) VALUES ('INF399','Topik Khusus Basis Data',2,6,'Pilihan',1); SELECT id_mk, kode_mk, nama_mk, sks FROM mata_kuliah WHERE kode_mk='INF399'; UPDATE mata_kuliah SET sks = 3 WHERE kode_mk = 'INF399'; SELECT kode_mk, sks, ROW_COUNT() AS baris_diubah FROM mata_kuliah WHERE kode_mk='INF399'; DELETE FROM mata_kuliah WHERE kode_mk = 'INF399'; SELECT COUNT(*) AS sisa FROM mata_kuliah WHERE kode_mk='INF399'; ROLLBACK;\"",
    ]),
    ("T15-uji-constraint", "Uji integritas: UNIQUE, FOREIGN KEY, CHECK", [
        f"{SQL} \"INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester_paket, id_prodi) VALUES ('INF301','Duplikat',3,3,1)\" 2>&1",
        f"{SQL} \"DELETE FROM mata_kuliah WHERE kode_mk = 'INF301'\" 2>&1",
        f"{SQL} \"INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester_paket, id_prodi) VALUES ('INF398','SKS Salah',9,3,1)\" 2>&1",
        f"{SQL} \"INSERT INTO krs (id_mahasiswa, id_kelas) VALUES (999, 1)\" 2>&1",
    ]),
    ("T16-data-unf", "Data gabungan (bentuk tidak normal) yang dipecah menjadi tabel 3NF", [
        f"{SQL} \"SELECT m.npm, m.nama_mahasiswa AS nama, p.nama_prodi AS prodi, f.kode_fakultas AS fak, mk.kode_mk, mk.nama_mk, mk.sks, d.nama_dosen AS pengampu, v.nilai_akhir AS na, v.huruf FROM krs k JOIN mahasiswa m ON m.id_mahasiswa=k.id_mahasiswa JOIN program_studi p ON p.id_prodi=m.id_prodi JOIN fakultas f ON f.id_fakultas=p.id_fakultas JOIN kelas kl ON kl.id_kelas=k.id_kelas JOIN mata_kuliah mk ON mk.id_mk=kl.id_mk JOIN dosen d ON d.id_dosen=kl.id_dosen JOIN v_nilai_akhir v ON v.id_krs=k.id_krs WHERE kl.id_ta=1 AND m.id_mahasiswa IN (1,2) ORDER BY m.npm, mk.kode_mk\"",
    ]),
]

CSS = """
body{margin:0;background:#e5e7eb;font-family:'JetBrains Mono','DejaVu Sans Mono',monospace}
.term{width:max-content;min-width:900px;max-width:1500px;margin:0;background:#0d1117;border-radius:10px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.25)}
.bar{background:#1f2937;padding:9px 14px;display:flex;align-items:center;gap:8px;color:#9ca3af;font:13px Inter,sans-serif}
.dot{width:12px;height:12px;border-radius:50%;display:inline-block}
.bar span.t{margin-left:10px}
pre{margin:0;padding:16px 18px 18px;color:#e6edf3;font-size:15px;line-height:1.45;white-space:pre}
.cmd{white-space:pre-wrap;max-width:1180px;word-break:break-word}
.p{color:#7ee787}.c{color:#79c0ff}
"""

for name, title, cmds in GROUPS:
    text_parts, html_parts = [], []
    for cmd in cmds:
        res = subprocess.run(cmd, shell=True, cwd=ROOT, capture_output=True, text=True)
        out = (res.stdout + res.stderr).rstrip()
        shown = cmd
        if shown.startswith(SQL):
            shown = "mariadb -t db_siakad -e " + shown[len(SQL):].strip()
        shown = shown.replace(" 2>&1", "")
        text_parts.append(f"$ {shown}\n{out}\n")
        html_parts.append(f'<div class="cmd"><span class="p">$</span> <span class="c">{html.escape(shown)}</span></div>{html.escape(out)}\n')
    with open(os.path.join(TXT_DIR, name + ".txt"), "w") as fh:
        fh.write("\n".join(text_parts))
    page = (f"<!doctype html><html><head><meta charset='utf-8'><style>{CSS}</style></head><body>"
            f"<div class='term'><div class='bar'><span class='dot' style='background:#ff5f56'></span>"
            f"<span class='dot' style='background:#ffbd2e'></span><span class='dot' style='background:#27c93f'></span>"
            f"<span class='t'>{html.escape(title)} · terminal server localhost</span></div>"
            f"<pre>{''.join(html_parts)}</pre></div></body></html>")
    with open(os.path.join(HTML_DIR, name + ".html"), "w") as fh:
        fh.write(page)
    print("captured", name)
