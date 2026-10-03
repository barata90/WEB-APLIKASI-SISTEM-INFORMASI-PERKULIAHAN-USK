<?php
class Nilai extends Model
{
    /**
     * Menyimpan nilai satu kelas sekaligus (UPSERT).
     * $rows: [id_krs => ['nilai_tugas' => .., 'nilai_uts' => .., 'nilai_uas' => ..]]
     * Hanya id_krs yang benar-benar milik kelas tersebut yang diproses.
     */
    public function simpanKelas(int $idKelas, array $rows): int
    {
        $valid = array_column(
            $this->fetchAll("SELECT id_krs FROM krs WHERE id_kelas = ? AND status = 'Disetujui'", [$idKelas]),
            'id_krs'
        );
        $valid = array_flip(array_map('intval', $valid));

        $stmt = $this->db->prepare(
            'INSERT INTO nilai (id_krs, nilai_tugas, nilai_uts, nilai_uas)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                nilai_tugas = VALUES(nilai_tugas),
                nilai_uts   = VALUES(nilai_uts),
                nilai_uas   = VALUES(nilai_uas)'
        );

        $this->db->beginTransaction();
        try {
            $count = 0;
            foreach ($rows as $idKrs => $n) {
                if (!isset($valid[(int) $idKrs])) {
                    continue;
                }
                $stmt->execute([(int) $idKrs, $n['nilai_tugas'], $n['nilai_uts'], $n['nilai_uas']]);
                $count++;
            }
            $this->db->commit();
            return $count;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function skala(): array
    {
        return $this->fetchAll('SELECT * FROM skala_nilai ORDER BY batas_bawah DESC');
    }
}
