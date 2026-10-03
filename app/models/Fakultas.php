<?php
class Fakultas extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            "SELECT f.*, COUNT(p.id_prodi) AS jumlah_prodi
             FROM fakultas f
             LEFT JOIN program_studi p ON p.id_fakultas = f.id_fakultas
             GROUP BY f.id_fakultas
             ORDER BY f.kode_fakultas"
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM fakultas WHERE id_fakultas = ?', [$id]);
    }

    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO fakultas (kode_fakultas, nama_fakultas) VALUES (?, ?)',
            [$d['kode_fakultas'], $d['nama_fakultas']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): int
    {
        return $this->execute(
            'UPDATE fakultas SET kode_fakultas = ?, nama_fakultas = ? WHERE id_fakultas = ?',
            [$d['kode_fakultas'], $d['nama_fakultas'], $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->execute('DELETE FROM fakultas WHERE id_fakultas = ?', [$id]);
    }
}
