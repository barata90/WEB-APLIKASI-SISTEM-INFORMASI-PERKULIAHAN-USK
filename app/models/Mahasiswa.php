<?php
class Mahasiswa extends Model
{
    /** READ: daftar mahasiswa dengan JOIN ke prodi, dosen wali, dan akun. */
    public function all(string $cari = '', ?int $idProdi = null, ?int $angkatan = null): array
    {
        $sql = "SELECT m.id_mahasiswa, m.npm, m.nama_mahasiswa, m.jenis_kelamin, m.angkatan, m.email,
                       p.nama_prodi, p.jenjang, d.nama_dosen AS dosen_wali, u.is_aktif,
                       ipk.ipk, ipk.total_sks
                FROM mahasiswa m
                JOIN program_studi p ON p.id_prodi = m.id_prodi
                LEFT JOIN dosen d    ON d.id_dosen = m.id_dosen_wali
                LEFT JOIN users u    ON u.id_user  = m.id_user
                LEFT JOIN v_ipk ipk  ON ipk.id_mahasiswa = m.id_mahasiswa
                WHERE 1 = 1";
        $params = [];
        if ($cari !== '') {
            $sql .= ' AND (m.npm LIKE ? OR m.nama_mahasiswa LIKE ?)';
            $params[] = "%$cari%";
            $params[] = "%$cari%";
        }
        if ($idProdi) {
            $sql .= ' AND m.id_prodi = ?';
            $params[] = $idProdi;
        }
        if ($angkatan) {
            $sql .= ' AND m.angkatan = ?';
            $params[] = $angkatan;
        }
        $sql .= ' ORDER BY m.angkatan DESC, m.npm';
        return $this->fetchAll($sql, $params);
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM mahasiswa WHERE id_mahasiswa = ?', [$id]);
    }

    /** Profil lengkap mahasiswa (untuk dashboard mahasiswa, KHS, transkrip). */
    public function profil(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT m.*, p.nama_prodi, p.jenjang, f.nama_fakultas, d.nama_dosen AS dosen_wali, d.nidn AS nidn_wali
             FROM mahasiswa m
             JOIN program_studi p ON p.id_prodi = m.id_prodi
             JOIN fakultas f      ON f.id_fakultas = p.id_fakultas
             LEFT JOIN dosen d    ON d.id_dosen = m.id_dosen_wali
             WHERE m.id_mahasiswa = ?",
            [$id]
        );
    }

    public function angkatanList(): array
    {
        return array_column($this->fetchAll('SELECT DISTINCT angkatan FROM mahasiswa ORDER BY angkatan DESC'), 'angkatan');
    }

    /** CREATE: tambah mahasiswa + akun login (username = NPM) dalam satu transaksi. */
    public function create(array $d, string $password): int
    {
        $this->db->beginTransaction();
        try {
            $this->execute(
                "INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'mahasiswa')",
                [$d['npm'], password_hash($password, PASSWORD_BCRYPT)]
            );
            $idUser = (int) $this->db->lastInsertId();

            $this->execute(
                'INSERT INTO mahasiswa
                    (npm, nama_mahasiswa, jenis_kelamin, tanggal_lahir, email, angkatan, id_prodi, id_dosen_wali, id_user)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $d['npm'], $d['nama_mahasiswa'], $d['jenis_kelamin'], nullable($d['tanggal_lahir']),
                    nullable($d['email']), $d['angkatan'], $d['id_prodi'], nullable($d['id_dosen_wali']), $idUser,
                ]
            );
            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** UPDATE: ubah biodata mahasiswa; username akun ikut diperbarui bila NPM berubah. */
    public function update(int $id, array $d): int
    {
        $this->db->beginTransaction();
        try {
            $n = $this->execute(
                'UPDATE mahasiswa
                 SET npm = ?, nama_mahasiswa = ?, jenis_kelamin = ?, tanggal_lahir = ?, email = ?,
                     angkatan = ?, id_prodi = ?, id_dosen_wali = ?
                 WHERE id_mahasiswa = ?',
                [
                    $d['npm'], $d['nama_mahasiswa'], $d['jenis_kelamin'], nullable($d['tanggal_lahir']),
                    nullable($d['email']), $d['angkatan'], $d['id_prodi'], nullable($d['id_dosen_wali']), $id,
                ]
            );
            $this->execute(
                'UPDATE users u JOIN mahasiswa m ON m.id_user = u.id_user
                 SET u.username = m.npm WHERE m.id_mahasiswa = ?',
                [$id]
            );
            $this->db->commit();
            return $n;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** DELETE: hapus mahasiswa beserta akunnya. Ditolak FK bila sudah punya KRS. */
    public function delete(int $id): int
    {
        $this->db->beginTransaction();
        try {
            $idUser = $this->scalar('SELECT id_user FROM mahasiswa WHERE id_mahasiswa = ?', [$id]);
            $n = $this->execute('DELETE FROM mahasiswa WHERE id_mahasiswa = ?', [$id]);
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

    /** Mahasiswa bimbingan seorang dosen wali beserta ringkasan KRS semester aktif. */
    public function bimbingan(int $idDosen, int $idTa): array
    {
        return $this->fetchAll(
            "SELECT m.id_mahasiswa, m.npm, m.nama_mahasiswa, m.angkatan, p.nama_prodi,
                    ipk.ipk,
                    COUNT(k.id_krs) AS jumlah_kelas,
                    COALESCE(SUM(mk.sks), 0) AS total_sks,
                    SUM(k.status = 'Diajukan')  AS jumlah_diajukan,
                    SUM(k.status = 'Disetujui') AS jumlah_disetujui
             FROM mahasiswa m
             JOIN program_studi p ON p.id_prodi = m.id_prodi
             LEFT JOIN v_ipk ipk ON ipk.id_mahasiswa = m.id_mahasiswa
             LEFT JOIN krs k ON k.id_mahasiswa = m.id_mahasiswa
                            AND k.id_kelas IN (SELECT id_kelas FROM kelas WHERE id_ta = ?)
             LEFT JOIN kelas kl ON kl.id_kelas = k.id_kelas
             LEFT JOIN mata_kuliah mk ON mk.id_mk = kl.id_mk
             WHERE m.id_dosen_wali = ?
             GROUP BY m.id_mahasiswa
             ORDER BY m.angkatan DESC, m.npm",
            [$idTa, $idDosen]
        );
    }
}
