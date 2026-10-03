<?php
class Prodi extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            "SELECT p.*, f.kode_fakultas, f.nama_fakultas,
                    (SELECT COUNT(*) FROM mahasiswa m WHERE m.id_prodi = p.id_prodi) AS jumlah_mahasiswa,
                    (SELECT COUNT(*) FROM dosen d WHERE d.id_prodi = p.id_prodi)     AS jumlah_dosen
             FROM program_studi p
             JOIN fakultas f ON f.id_fakultas = p.id_fakultas
             ORDER BY f.kode_fakultas, p.nama_prodi"
        );
    }

    public function options(): array
    {
        return $this->fetchAll(
            "SELECT id_prodi, CONCAT(jenjang, ' ', nama_prodi) AS label FROM program_studi ORDER BY nama_prodi"
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM program_studi WHERE id_prodi = ?', [$id]);
    }

    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO program_studi (kode_prodi, nama_prodi, jenjang, id_fakultas) VALUES (?, ?, ?, ?)',
            [$d['kode_prodi'], $d['nama_prodi'], $d['jenjang'], $d['id_fakultas']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): int
    {
        return $this->execute(
            'UPDATE program_studi SET kode_prodi = ?, nama_prodi = ?, jenjang = ?, id_fakultas = ? WHERE id_prodi = ?',
            [$d['kode_prodi'], $d['nama_prodi'], $d['jenjang'], $d['id_fakultas'], $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->execute('DELETE FROM program_studi WHERE id_prodi = ?', [$id]);
    }
}
