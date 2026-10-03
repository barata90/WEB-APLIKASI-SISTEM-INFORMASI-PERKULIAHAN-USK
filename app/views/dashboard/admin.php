<?php
$cards = [
    ['Mahasiswa', $ringkasan['mahasiswa'] ?? 0, 'users', 'admin/mahasiswa', 'c-blue'],
    ['Dosen', $ringkasan['dosen'] ?? 0, 'user', 'admin/dosen', 'c-green'],
    ['Mata Kuliah', $ringkasan['mata_kuliah'] ?? 0, 'book', 'admin/mata-kuliah', 'c-amber'],
    ['Kelas Semester Ini', $ringkasan['kelas_aktif'] ?? 0, 'grid', 'admin/kelas', 'c-purple'],
];
$maxProdi = max(array_column($perProdi, 'jumlah') ?: [1]) ?: 1;
$maxHuruf = max(array_column($sebaran, 'jumlah') ?: [1]) ?: 1;
$totalNilai = array_sum(array_column($sebaran, 'jumlah'));
?>
<div class="stat-grid">
    <?php foreach ($cards as [$label, $value, $ic, $link, $color]): ?>
        <a class="stat-card <?= $color ?>" href="<?= e(url($link)) ?>">
            <div class="stat-icon"><?= icon($ic, 22) ?></div>
            <div>
                <div class="stat-value"><?= e($value) ?></div>
                <div class="stat-label"><?= e($label) ?></div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<div class="grid-2">
    <section class="card">
        <div class="card-head"><h2>Jumlah Mahasiswa per Program Studi</h2></div>
        <div class="bars">
            <?php foreach ($perProdi as $p): ?>
                <div class="bar-row">
                    <span class="bar-label"><?= e($p['nama_prodi']) ?></span>
                    <span class="bar-track"><span class="bar-fill" style="width: <?= round($p['jumlah'] / $maxProdi * 100) ?>%"></span></span>
                    <span class="bar-value"><?= e($p['jumlah']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="card">
        <div class="card-head"><h2>Sebaran Huruf Mutu (semua semester)</h2></div>
        <div class="bars">
            <?php foreach ($sebaran as $s): ?>
                <div class="bar-row">
                    <span class="bar-label"><span class="badge <?= badge_class($s['huruf']) ?>"><?= e($s['huruf']) ?></span></span>
                    <span class="bar-track"><span class="bar-fill alt" style="width: <?= round($s['jumlah'] / $maxHuruf * 100) ?>%"></span></span>
                    <span class="bar-value"><?= e($s['jumlah']) ?> <small class="muted">(<?= $totalNilai ? angka($s['jumlah'] / $totalNilai * 100, 1) : 0 ?>%)</small></span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="muted small">Total <?= e($totalNilai) ?> nilai lengkap (tugas, UTS, UAS) dari KRS yang disetujui.</p>
    </section>
</div>

<div class="grid-3">
    <section class="card">
        <div class="card-head"><h2>Struktur Akademik</h2></div>
        <dl class="kv">
            <dt>Fakultas</dt><dd><?= e($ringkasan['fakultas'] ?? 0) ?></dd>
            <dt>Program studi</dt><dd><?= e($ringkasan['prodi'] ?? 0) ?></dd>
            <dt>Tahun akademik aktif</dt><dd><?= $ta ? e($ta['tahun'] . ' ' . $ta['semester']) : '-' ?></dd>
        </dl>
    </section>
    <section class="card">
        <div class="card-head"><h2>Akun Pengguna</h2></div>
        <dl class="kv">
            <dt>Administrator</dt><dd><?= e($akun['admin'] ?? 0) ?></dd>
            <dt>Dosen</dt><dd><?= e($akun['dosen'] ?? 0) ?></dd>
            <dt>Mahasiswa</dt><dd><?= e($akun['mahasiswa'] ?? 0) ?></dd>
        </dl>
    </section>
    <section class="card">
        <div class="card-head"><h2>Perlu Perhatian</h2></div>
        <dl class="kv">
            <dt>KRS menunggu persetujuan</dt><dd><span class="badge badge-info"><?= e($ringkasan['krs_menunggu'] ?? 0) ?></span></dd>
        </dl>
        <div class="quick-links">
            <a class="btn btn-sm btn-primary" href="<?= e(url('admin/mahasiswa', ['action' => 'create'])) ?>"><?= icon('plus', 15) ?><span>Mahasiswa</span></a>
            <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/kelas', ['action' => 'create'])) ?>"><?= icon('plus', 15) ?><span>Kelas</span></a>
        </div>
    </section>
</div>
