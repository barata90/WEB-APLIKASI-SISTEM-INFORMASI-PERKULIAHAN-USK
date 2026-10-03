<?php
$user = Auth::user();
$role = $user['role'];
$taAktif = (new TahunAkademik())->aktif();
$menu = [
    'admin' => [
        ['Dashboard', 'dashboard', 'home'],
        ['__', 'Data Master'],
        ['Fakultas', 'admin/fakultas', 'building'],
        ['Program Studi', 'admin/prodi', 'layers'],
        ['Dosen', 'admin/dosen', 'user'],
        ['Mahasiswa', 'admin/mahasiswa', 'users'],
        ['Mata Kuliah', 'admin/mata-kuliah', 'book'],
        ['Ruangan', 'admin/ruangan', 'map'],
        ['__', 'Akademik'],
        ['Tahun Akademik', 'admin/tahun-akademik', 'calendar'],
        ['Kelas & Jadwal', 'admin/kelas', 'grid'],
        ['__', 'Sistem'],
        ['Pengguna', 'admin/pengguna', 'key'],
        ['Informasi Sistem', 'admin/sistem', 'server'],
    ],
    'dosen' => [
        ['Dashboard', 'dashboard', 'home'],
        ['__', 'Pengajaran'],
        ['Kelas & Input Nilai', 'dosen/kelas', 'clipboard'],
        ['Perwalian (KRS)', 'dosen/perwalian', 'users'],
    ],
    'mahasiswa' => [
        ['Dashboard', 'dashboard', 'home'],
        ['__', 'Akademik'],
        ['Isi KRS', 'mahasiswa/krs', 'clipboard'],
        ['Kartu Hasil Studi', 'mahasiswa/khs', 'file'],
        ['Transkrip Nilai', 'mahasiswa/transkrip', 'award'],
    ],
][$role];
$roleLabel = ['admin' => 'Administrator', 'dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa'][$role];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'SIAKAD') ?> · <?= e(config('app_name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="icon" href="<?= e(asset('img/logo.svg')) ?>">
</head>
<body class="role-<?= e($role) ?>">
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?= e(url('dashboard')) ?>">
            <img src="<?= e(asset('img/logo.svg')) ?>" alt="" width="36" height="36">
            <span>
                <strong><?= e(config('app_name')) ?></strong>
                <small><?= e(config('app_subtitle')) ?></small>
            </span>
        </a>
        <nav class="nav">
            <?php foreach ($menu as $item): ?>
                <?php if ($item[0] === '__'): ?>
                    <div class="nav-section"><?= e($item[1]) ?></div>
                <?php else: ?>
                    <a class="nav-link<?= $item[1] === 'dashboard' ? (($_GET['page'] ?? 'dashboard') === 'dashboard' ? ' active' : '') : is_active_page($item[1]) ?>"
                       href="<?= e(url($item[1])) ?>"><?= icon($item[2]) ?><span><?= e($item[0]) ?></span></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-foot">Proyek UTS Manajemen &amp; Pemodelan Data</div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="menu-toggle" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menu">&#9776;</button>
            <div class="topbar-ta">
                <?= icon('calendar', 16) ?>
                <?php if ($taAktif): ?>
                    TA aktif: <strong><?= e($taAktif['tahun']) ?> <?= e($taAktif['semester']) ?></strong>
                <?php else: ?>
                    Belum ada tahun akademik aktif
                <?php endif; ?>
            </div>
            <div class="topbar-user">
                <a href="<?= e(url('profil')) ?>" class="user-chip">
                    <span class="avatar"><?= e(mb_strtoupper(mb_substr(preg_replace('/^(Dr\.|Ir\.)\s*/', '', $user['nama']), 0, 1))) ?></span>
                    <span class="user-meta">
                        <strong><?= e($user['nama']) ?></strong>
                        <small><?= e($roleLabel) ?> · <?= e($user['username']) ?></small>
                    </span>
                </a>
                <form method="post" action="<?= e(url('logout', ['action' => 'logout'])) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-ghost" type="submit"><?= icon('logout', 16) ?><span>Keluar</span></button>
                </form>
            </div>
        </header>

        <main class="content">
            <div class="page-head">
                <h1><?= e($title ?? '') ?></h1>
                <?php if (!empty($subtitle)): ?><p class="muted"><?= e($subtitle) ?></p><?php endif; ?>
            </div>
            <?php foreach (['success', 'error'] as $type): ?>
                <?php if ($msg = flash($type)): ?>
                    <div class="alert alert-<?= $type ?>" role="alert">
                        <?= icon($type === 'success' ? 'check' : 'x', 16) ?><span><?= e($msg) ?></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
        <footer class="footer">
            &copy; <?= date('Y') ?> <?= e(config('app_name')) ?> · <?= e(config('app_subtitle')) ?> · PHP <?= PHP_VERSION ?>
        </footer>
    </div>
</div>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
