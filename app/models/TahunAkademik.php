<?php
class TahunAkademik extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            "SELECT t.*, (SELECT COUNT(*) FROM kelas k WHERE k.id_ta = t.id_ta) AS jumlah_kelas
             FROM tahun_akademik t
             ORDER BY t.tahun DESC, t.semester DESC"
        );
    }

    public function options(): array
    {
        return $this->fetchAll(
            "SELECT id_ta, CONCAT(tahun, ' ', semester, IF(is_aktif = 1, ' (aktif)', '')) AS label
             FROM tahun_akademik ORDER BY tahun DESC, semester DESC"
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM tahun_akademik WHERE id_ta = ?', [$id]);
    }

    /** Tahun akademik yang sedang aktif (hanya boleh satu). */
    public function aktif(): ?array
    {
        return $this->fetchOne('SELECT * FROM tahun_akademik WHERE is_aktif = 1 LIMIT 1');
    }

    public function create(array $d): int
    {
        $this->db->beginTransaction();
        try {
            if (!empty($d['is_aktif'])) {
                $this->execute('UPDATE tahun_akademik SET is_aktif = 0');
            }
            $this->execute(
                'INSERT INTO tahun_akademik (tahun, semester, is_aktif) VALUES (?, ?, ?)',
                [$d['tahun'], $d['semester'], empty($d['is_aktif']) ? 0 : 1]
            );
            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $d): int
    {
        $this->db->beginTransaction();
        try {
            if (!empty($d['is_aktif'])) {
                $this->execute('UPDATE tahun_akademik SET is_aktif = 0 WHERE id_ta <> ?', [$id]);
            }
            $n = $this->execute(
                'UPDATE tahun_akademik SET tahun = ?, semester = ?, is_aktif = ? WHERE id_ta = ?',
                [$d['tahun'], $d['semester'], empty($d['is_aktif']) ? 0 : 1, $id]
            );
            $this->db->commit();
            return $n;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): int
    {
        return $this->execute('DELETE FROM tahun_akademik WHERE id_ta = ?', [$id]);
    }
}
