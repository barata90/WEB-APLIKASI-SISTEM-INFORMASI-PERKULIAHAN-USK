<?php
/** Query agregat untuk dashboard dan halaman info sistem. */
class Statistik extends Model
{
    public function ringkasan(?int $idTa): array
    {
        return $this->fetchOne(
            "SELECT
                (SELECT COUNT(*) FROM fakultas)      AS fakultas,
                (SELECT COUNT(*) FROM program_studi) AS prodi,
                (SELECT COUNT(*) FROM dosen)         AS dosen,
                (SELECT COUNT(*) FROM mahasiswa)     AS mahasiswa,
                (SELECT COUNT(*) FROM mata_kuliah)   AS mata_kuliah,
                (SELECT COUNT(*) FROM kelas WHERE id_ta = ?) AS kelas_aktif,
                (SELECT COUNT(*) FROM krs k JOIN kelas kl ON kl.id_kelas = k.id_kelas
                  WHERE kl.id_ta = ? AND k.status = 'Diajukan') AS krs_menunggu",
            [$idTa, $idTa]
        ) ?? [];
    }

    public function mahasiswaPerProdi(): array
    {
        return $this->fetchAll(
            "SELECT p.nama_prodi, COUNT(m.id_mahasiswa) AS jumlah
             FROM program_studi p
             LEFT JOIN mahasiswa m ON m.id_prodi = p.id_prodi
             GROUP BY p.id_prodi
             ORDER BY jumlah DESC, p.nama_prodi"
        );
    }

    public function sebaranHuruf(?int $idTa = null): array
    {
        $sql = "SELECT s.huruf, COUNT(v.id_nilai) AS jumlah
                FROM skala_nilai s
                LEFT JOIN v_nilai_akhir v ON v.huruf = s.huruf" . ($idTa ? ' AND v.id_ta = ?' : '') . "
                GROUP BY s.huruf, s.batas_bawah
                ORDER BY s.batas_bawah DESC";
        return $this->fetchAll($sql, $idTa ? [$idTa] : []);
    }

    public function tabelDatabase(): array
    {
        return $this->fetchAll(
            "SELECT TABLE_NAME AS nama, TABLE_TYPE AS jenis, ENGINE AS engine, TABLE_ROWS AS perkiraan_baris,
                    TABLE_COLLATION AS collation
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
             ORDER BY TABLE_TYPE, TABLE_NAME"
        );
    }

    public function jumlahBaris(string $table): int
    {
        // Nama tabel berasal dari information_schema, bukan dari input pengguna.
        return (int) $this->scalar('SELECT COUNT(*) FROM `' . str_replace('`', '', $table) . '`');
    }

    public function foreignKeys(): array
    {
        return $this->fetchAll(
            "SELECT k.TABLE_NAME AS tabel, k.COLUMN_NAME AS kolom, k.REFERENCED_TABLE_NAME AS ref_tabel,
                    k.REFERENCED_COLUMN_NAME AS ref_kolom, k.CONSTRAINT_NAME AS nama,
                    r.UPDATE_RULE AS on_update, r.DELETE_RULE AS on_delete
             FROM information_schema.KEY_COLUMN_USAGE k
             JOIN information_schema.REFERENTIAL_CONSTRAINTS r
               ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
             WHERE k.TABLE_SCHEMA = DATABASE() AND k.REFERENCED_TABLE_NAME IS NOT NULL
             ORDER BY k.TABLE_NAME, k.COLUMN_NAME"
        );
    }

    public function versiDatabase(): string
    {
        return (string) $this->scalar('SELECT VERSION()');
    }
}
