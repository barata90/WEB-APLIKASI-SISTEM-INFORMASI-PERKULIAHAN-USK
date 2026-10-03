<?php
/**
 * Autentikasi berbasis session dan kontrol akses berbasis peran (admin, dosen, mahasiswa).
 */
final class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            "SELECT u.id_user, u.username, u.password_hash, u.role,
                    d.id_dosen, m.id_mahasiswa,
                    COALESCE(d.nama_dosen, m.nama_mahasiswa, 'Administrator') AS nama
             FROM users u
             LEFT JOIN dosen d     ON d.id_user = u.id_user
             LEFT JOIN mahasiswa m ON m.id_user = u.id_user
             WHERE u.username = ? AND u.is_aktif = 1
             LIMIT 1"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $db->prepare('UPDATE users SET last_login = NOW() WHERE id_user = ?')->execute([$user['id_user']]);
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function id(string $key = 'id_user'): ?int
    {
        return isset($_SESSION['user'][$key]) ? (int) $_SESSION['user'][$key] : null;
    }
}
