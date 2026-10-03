<?php
class User extends Model
{
    public function all(string $role = ''): array
    {
        $sql = "SELECT u.id_user, u.username, u.role, u.is_aktif, u.last_login, u.created_at,
                       COALESCE(d.nama_dosen, m.nama_mahasiswa, 'Administrator') AS nama
                FROM users u
                LEFT JOIN dosen d     ON d.id_user = u.id_user
                LEFT JOIN mahasiswa m ON m.id_user = u.id_user";
        $params = [];
        if ($role !== '') {
            $sql .= ' WHERE u.role = ?';
            $params[] = $role;
        }
        return $this->fetchAll($sql . " ORDER BY FIELD(u.role, 'admin', 'dosen', 'mahasiswa'), u.username", $params);
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE id_user = ?', [$id]);
    }

    public function createAdmin(string $username, string $password): int
    {
        $this->execute(
            "INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')",
            [$username, password_hash($password, PASSWORD_BCRYPT)]
        );
        return (int) $this->db->lastInsertId();
    }

    public function setPassword(int $id, string $password): int
    {
        return $this->execute(
            'UPDATE users SET password_hash = ? WHERE id_user = ?',
            [password_hash($password, PASSWORD_BCRYPT), $id]
        );
    }

    public function verifyPassword(int $id, string $password): bool
    {
        $hash = $this->scalar('SELECT password_hash FROM users WHERE id_user = ?', [$id]);
        return $hash && password_verify($password, $hash);
    }

    public function toggleAktif(int $id): int
    {
        return $this->execute('UPDATE users SET is_aktif = 1 - is_aktif WHERE id_user = ?', [$id]);
    }

    public function countByRole(): array
    {
        return array_column($this->fetchAll('SELECT role, COUNT(*) AS n FROM users GROUP BY role'), 'n', 'role');
    }
}
