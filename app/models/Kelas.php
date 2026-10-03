<?php
class Kelas extends Model
{
    private const SELECT = "
        SELECT kl.*, mk.kode_mk, mk.nama_mk, mk.sks, mk.semester_paket, mk.id_prodi,
               d.nama_dosen, d.nidn, r.kode_ruangan, r.nama_ruangan,
               t.tahun, t.semester, t.is_aktif,
               (SELECT COUNT(*) FROM krs k WHERE k.id_kelas = kl.id_kelas AND k.status <> 'Ditolak') AS jumlah_peserta
        FROM kelas kl
        JOIN mata_kuliah mk    ON mk.id_mk = kl.id_mk
        JOIN dosen d           ON d.id_dosen = kl.id_dosen
        JOIN tahun_akademik t  ON t.id_ta = kl.id_ta
        LEFT JOIN ruangan r    ON r.id_ruangan = kl.id_ruangan";

    private const ORDER = " ORDER BY FIELD(kl.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), kl.jam_mulai, mk.kode_mk";

    public function all(?int $idTa = null, ?int $idProdi = null): array
    {
        $sql = self::SELECT . ' WHERE 1 = 1';
        $params = [];
        if ($idTa) {
            $sql .= ' AND kl.id_ta = ?';
            $params[] = $idTa;
        }
        if ($idProdi) {
            $sql .= ' AND mk.id_prodi = ?';
            $params[] = $idProdi;
        }
        return $this->fetchAll($sql . self::ORDER, $params);
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(self::SELECT . ' WHERE kl.id_kelas = ?', [$id]);
    }

    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO kelas (id_mk, id_ta, id_dosen, id_ruangan, nama_kelas, hari, jam_mulai, jam_selesai, kuota)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$d['id_mk'], $d['id_ta'], $d['id_dosen'], nullable($d['id_ruangan']), $d['nama_kelas'],
             $d['hari'], $d['jam_mulai'], $d['jam_selesai'], $d['kuota']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): int
    {
        return $this->execute(
            'UPDATE kelas
             SET id_mk = ?, id_ta = ?, id_dosen = ?, id_ruangan = ?, nama_kelas = ?,
                 hari = ?, jam_mulai = ?, jam_selesai = ?, kuota = ?
             WHERE id_kelas = ?',
            [$d['id_mk'], $d['id_ta'], $d['id_dosen'], nullable($d['id_ruangan']), $d['nama_kelas'],
             $d['hari'], $d['jam_mulai'], $d['jam_selesai'], $d['kuota'], $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->execute('DELETE FROM kelas WHERE id_kelas = ?', [$id]);
    }

    /**
     * Mencari kelas lain yang bentrok: ruangan sama atau dosen sama,
     * pada tahun akademik dan hari yang sama, dengan rentang jam yang beririsan.
     */
    public function bentrok(array $d, ?int $exceptId = null): ?array
    {
        return $this->fetchOne(
            "SELECT kl.id_kelas, mk.nama_mk, kl.nama_kelas, kl.hari, kl.jam_mulai, kl.jam_selesai,
                    IF(kl.id_dosen = ?, 'dosen', 'ruangan') AS jenis
             FROM kelas kl
             JOIN mata_kuliah mk ON mk.id_mk = kl.id_mk
             WHERE kl.id_ta = ? AND kl.hari = ?
               AND kl.jam_mulai < ? AND kl.jam_selesai > ?
               AND (kl.id_dosen = ? OR (kl.id_ruangan IS NOT NULL AND kl.id_ruangan = ?))
               AND kl.id_kelas <> ?
             LIMIT 1",
            [$d['id_dosen'], $d['id_ta'], $d['hari'], $d['jam_selesai'], $d['jam_mulai'],
             $d['id_dosen'], nullable($d['id_ruangan']), $exceptId ?? 0]
        );
    }

    /** Kelas yang diampu dosen pada tahun akademik tertentu. */
    public function byDosen(int $idDosen, ?int $idTa = null): array
    {
        $sql = self::SELECT . ' WHERE kl.id_dosen = ?';
        $params = [$idDosen];
        if ($idTa) {
            $sql .= ' AND kl.id_ta = ?';
            $params[] = $idTa;
        }
        return $this->fetchAll($sql . self::ORDER, $params);
    }

    /** Kelas yang dapat diambil mahasiswa: TA aktif dan prodi yang sama. */
    public function tersedia(int $idMahasiswa, int $idTa): array
    {
        return $this->fetchAll(
            self::SELECT . "
             WHERE kl.id_ta = ?
               AND mk.id_prodi = (SELECT id_prodi FROM mahasiswa WHERE id_mahasiswa = ?)
               AND kl.id_kelas NOT IN (SELECT id_kelas FROM krs WHERE id_mahasiswa = ?)" . self::ORDER,
            [$idTa, $idMahasiswa, $idMahasiswa]
        );
    }

    /** Peserta kelas (KRS disetujui) beserta komponen nilai, nilai akhir, dan huruf mutu. */
    public function peserta(int $idKelas): array
    {
        return $this->fetchAll(
            "SELECT k.id_krs, m.npm, m.nama_mahasiswa,
                    n.nilai_tugas, n.nilai_uts, n.nilai_uas,
                    v.nilai_akhir, v.huruf
             FROM krs k
             JOIN mahasiswa m         ON m.id_mahasiswa = k.id_mahasiswa
             LEFT JOIN nilai n        ON n.id_krs = k.id_krs
             LEFT JOIN v_nilai_akhir v ON v.id_krs = k.id_krs
             WHERE k.id_kelas = ? AND k.status = 'Disetujui'
             ORDER BY m.npm",
            [$idKelas]
        );
    }
}
