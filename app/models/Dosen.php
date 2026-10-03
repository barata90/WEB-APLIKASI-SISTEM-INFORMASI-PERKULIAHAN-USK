<?php
class Dosen extends Model
{
    public function all(string $cari = ''): array
    {
        $sql = "SELECT d.*, p.nama_prodi, u.username, u.is_aktif,
                       (SELECT COUNT(*) FROM mahasiswa m WHERE m.id_dosen_wali = d.id_dosen) AS jumlah_bimbingan
                FROM dosen d
                JOIN program_studi p ON p.id_prodi = d.id_prodi
                LEFT JOIN users u    ON u.id_user  = d.id_user";
        $params = [];
        if ($cari !== '') {
            $sql .= ' WHERE d.nidn LIKE ? OR d.nama_dosen LIKE ?';
            $params = ["%$cari%", "%$cari%"];
        }
        return $this->fetchAll($sql . ' ORDER BY d.nama_dosen', $params);
    }

    public function options(): array
    {
        return $this->fetchAll('SELECT id_dosen, nidn, nama_dosen FROM dosen ORDER BY nama_dosen');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM dosen WHERE id_dosen = ?', [$id]);
    }

    /**
     * Menambah dosen sekaligus membuat akun login (username = NIDN).
     * Kedua INSERT dibungkus dalam satu transaksi agar konsisten.
     */
    public function create(array $d, string $password): int
    {
        $this->db->beginTransaction();
        try {
            $this->execute(
                "INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'dosen')",
                [$d['nidn'], password_hash($password, PASSWORD_BCRYPT)]
            );
            $idUser = (int) $this->db->lastInsertId();

            $this->execute(
                'INSERT INTO dosen (nidn, nama_dosen, email, no_hp, id_prodi, id_user)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [$d['nidn'], $d['nama_dosen'], nullable($d['email']), nullable($d['no_hp']), $d['id_prodi'], $idUser]
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
            $n = $this->execute(
                'UPDATE dosen SET nidn = ?, nama_dosen = ?, email = ?, no_hp = ?, id_prodi = ? WHERE id_dosen = ?',
                [$d['nidn'], $d['nama_dosen'], nullable($d['email']), nullable($d['no_hp']), $d['id_prodi'], $id]
            );
            // username mengikuti NIDN
            $this->execute(
                'UPDATE users u JOIN dosen d ON d.id_user = u.id_user SET u.username = d.nidn WHERE d.id_dosen = ?',
                [$id]
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
        $this->db->beginTransaction();
        try {
            $idUser = $this->scalar('SELECT id_user FROM dosen WHERE id_dosen = ?', [$id]);
            $n = $this->execute('DELETE FROM dosen WHERE id_dosen = ?', [$id]);
            if ($idUser) {
                $this->execute('DELETE FROM users WHERE id_user = ?', [$idUser]);
            }
            $this->db->commit();
            return $n;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
