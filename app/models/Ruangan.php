<?php
class Ruangan extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM ruangan ORDER BY kode_ruangan');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM ruangan WHERE id_ruangan = ?', [$id]);
    }

    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO ruangan (kode_ruangan, nama_ruangan, gedung, kapasitas) VALUES (?, ?, ?, ?)',
            [$d['kode_ruangan'], $d['nama_ruangan'], nullable($d['gedung']), $d['kapasitas']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): int
    {
        return $this->execute(
            'UPDATE ruangan SET kode_ruangan = ?, nama_ruangan = ?, gedung = ?, kapasitas = ? WHERE id_ruangan = ?',
            [$d['kode_ruangan'], $d['nama_ruangan'], nullable($d['gedung']), $d['kapasitas'], $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->execute('DELETE FROM ruangan WHERE id_ruangan = ?', [$id]);
    }
}
