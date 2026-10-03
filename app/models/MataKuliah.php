<?php
class MataKuliah extends Model
{
    public function all(string $cari = '', ?int $idProdi = null): array
    {
        $sql = "SELECT mk.*, p.nama_prodi, p.jenjang
                FROM mata_kuliah mk
                JOIN program_studi p ON p.id_prodi = mk.id_prodi
                WHERE 1 = 1";
        $params = [];
        if ($cari !== '') {
            $sql .= ' AND (mk.kode_mk LIKE ? OR mk.nama_mk LIKE ?)';
            $params[] = "%$cari%";
            $params[] = "%$cari%";
        }
        if ($idProdi) {
            $sql .= ' AND mk.id_prodi = ?';
            $params[] = $idProdi;
        }
        $sql .= ' ORDER BY p.nama_prodi, mk.semester_paket, mk.kode_mk';
        return $this->fetchAll($sql, $params);
    }

    public function options(): array
    {
        return $this->fetchAll(
            "SELECT mk.id_mk, CONCAT(mk.kode_mk, ' - ', mk.nama_mk, ' (', mk.sks, ' SKS)') AS label
             FROM mata_kuliah mk ORDER BY mk.kode_mk"
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM mata_kuliah WHERE id_mk = ?', [$id]);
    }

    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester_paket, jenis, id_prodi)
             VALUES (?, ?, ?, ?, ?, ?)',
            [strtoupper($d['kode_mk']), $d['nama_mk'], $d['sks'], $d['semester_paket'], $d['jenis'], $d['id_prodi']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): int
    {
        return $this->execute(
            'UPDATE mata_kuliah
             SET kode_mk = ?, nama_mk = ?, sks = ?, semester_paket = ?, jenis = ?, id_prodi = ?
             WHERE id_mk = ?',
            [strtoupper($d['kode_mk']), $d['nama_mk'], $d['sks'], $d['semester_paket'], $d['jenis'], $d['id_prodi'], $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->execute('DELETE FROM mata_kuliah WHERE id_mk = ?', [$id]);
    }
}
