<?php
/**
 * Fungsi bantu global: konfigurasi, URL, escaping, flash message, CSRF, dan format.
 */

function config(?string $key = null): mixed
{
    static $config = null;
    $config ??= require BASE_PATH . '/config/config.php';
    return $key === null ? $config : ($config[$key] ?? null);
}

/** Escape output HTML untuk mencegah XSS. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL dasar aplikasi, dihitung otomatis dari lokasi index.php. */
function base_url(string $path = ''): string
{
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $dir . '/' . ltrim($path, '/');
}

/** Membuat URL halaman: url('admin/mahasiswa', ['action' => 'edit', 'id' => 3]). */
function url(string $page, array $params = []): string
{
    $query = http_build_query(array_merge(['page' => $page], $params));
    return base_url('index.php') . '?' . $query;
}

function asset(string $path): string
{
    $file = BASE_PATH . '/assets/' . $path;
    $version = is_file($file) ? filemtime($file) : 1;
    return base_url('assets/' . $path) . '?v=' . $version;
}

function redirect(string $page, array $params = []): never
{
    header('Location: ' . url($page, $params));
    exit;
}

function abort(int $code, string $message = '', ?string $title = null): never
{
    http_response_code($code);
    $title ??= match ($code) {
        403 => 'Akses Ditolak',
        404 => 'Halaman Tidak Ditemukan',
        405 => 'Metode Tidak Diizinkan',
        default => 'Terjadi Kesalahan',
    };
    require APP_PATH . '/views/errors/error.php';
    exit;
}

function flash(string $type, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$type] = $message;
        return null;
    }
    $msg = $_SESSION['_flash'][$type] ?? null;
    unset($_SESSION['_flash'][$type]);
    return $msg;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        abort(403, 'Token formulir tidak valid atau sesi telah berakhir. Silakan muat ulang halaman.', 'Sesi Kedaluwarsa');
    }
}

/** Nilai input lama (setelah validasi gagal) atau nilai bawaan. */
function old(string $field, mixed $default = ''): string
{
    if (isset($GLOBALS['_old'][$field])) {
        return (string) $GLOBALS['_old'][$field];
    }
    return (string) ($default ?? '');
}

function error_for(string $field): string
{
    $msg = $GLOBALS['_errors'][$field] ?? null;
    return $msg ? '<div class="field-error">' . e($msg) . '</div>' : '';
}

function has_error(string $field): string
{
    return isset($GLOBALS['_errors'][$field]) ? ' is-invalid' : '';
}

function input(string $key, mixed $default = null): mixed
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}

/** Ubah string kosong menjadi NULL sebelum disimpan. */
function nullable(mixed $value): mixed
{
    return $value === '' || $value === null ? null : $value;
}

function tanggal_indo(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '-';
    }
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $ts = strtotime($date);
    $out = date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    return $withTime ? $out . ', ' . date('H:i', $ts) : $out;
}

function jam(?string $time): string
{
    return $time ? substr($time, 0, 5) : '-';
}

function angka(mixed $value, int $decimals = 2): string
{
    return $value === null || $value === '' ? '-' : number_format((float) $value, $decimals, ',', '.');
}

function is_active_page(string $prefix): string
{
    $page = $_GET['page'] ?? 'dashboard';
    return str_starts_with($page, $prefix) ? ' active' : '';
}

/** Kelas CSS badge untuk huruf mutu / status. */
function badge_class(?string $value): string
{
    return match ($value) {
        'A', 'AB', 'Disetujui', 'Aktif' => 'badge-success',
        'B', 'BC', 'Diajukan'           => 'badge-info',
        'C', 'Nonaktif'                 => 'badge-warning',
        'D', 'E', 'Ditolak'             => 'badge-danger',
        default                         => 'badge-muted',
    };
}

/** Render <select> dari array baris. */
function select_options(array $rows, string $valueKey, string|callable $labelKey, mixed $selected, string $placeholder = '-- Pilih --'): string
{
    $html = $placeholder !== '' ? '<option value="">' . e($placeholder) . '</option>' : '';
    foreach ($rows as $r) {
        $value = (string) $r[$valueKey];
        $label = is_callable($labelKey) ? $labelKey($r) : $r[$labelKey];
        $sel = $value === (string) $selected ? ' selected' : '';
        $html .= '<option value="' . e($value) . '"' . $sel . '>' . e($label) . '</option>';
    }
    return $html;
}

/** Pesan ramah untuk galat basis data yang umum (duplikat, FK). */
function db_error_message(PDOException $e): string
{
    $code = $e->errorInfo[1] ?? 0;
    return match ((int) $code) {
        1062 => 'Data dengan kode/nomor yang sama sudah ada (melanggar UNIQUE).',
        1451 => 'Data tidak dapat dihapus karena masih dipakai oleh data lain (foreign key).',
        1452 => 'Data rujukan tidak ditemukan (foreign key tidak valid).',
        3819, 4025 => 'Nilai melanggar aturan CHECK pada basis data.',
        default => config('debug') ? $e->getMessage() : 'Terjadi kesalahan basis data.',
    };
}

/** Ikon SVG sederhana (garis), berbasis gaya Feather Icons (lisensi MIT). */
function icon(string $name, int $size = 18): string
{
    $paths = [
        'home'     => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'building' => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/>',
        'layers'   => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'user'     => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'users'    => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'book'     => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'map'      => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'grid'     => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'key'      => '<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>',
        'server'   => '<rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>',
        'edit'     => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'trash'    => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'plus'     => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'search'   => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'clipboard'=> '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/>',
        'award'    => '<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>',
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'check'    => '<polyline points="20 6 9 17 4 12"/>',
        'x'        => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'printer'  => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'refresh'  => '<polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>',
        'eye'      => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'save'     => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
    ];
    $p = $paths[$name] ?? $paths['grid'];
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

/** Tombol hapus dalam bentuk form POST dengan konfirmasi. */
function delete_button(string $page, int $id, string $label = 'Hapus', string $confirm = 'Yakin ingin menghapus data ini?'): string
{
    return '<form method="post" action="' . e(url($page, ['action' => 'delete', 'id' => $id])) . '" class="inline" onsubmit="return confirm(\'' . e($confirm) . '\')">'
        . csrf_field()
        . '<button type="submit" class="btn btn-sm btn-danger-ghost" title="' . e($label) . '">' . icon('trash', 15) . '<span>' . e($label) . '</span></button></form>';
}
