<?php
$sksSemester = array_sum(array_map(fn($r) => $r['status'] !== 'Ditolak' ? $r['sks'] : 0, $krs));
$statusKrs = array_count_values(array_column($krs, 'status'));
?>
<div class="profile-banner">
    <div class="avatar avatar-lg"><?= e(mb_strtoupper(mb_substr($profil['nama_mahasiswa'], 0, 1))) ?></div>
    <div>
        <h2><?= e($profil['nama_mahasiswa']) ?></h2>
        <p><?= e($profil['npm']) ?> · <?= e($profil['jenjang'] . ' ' . $profil['nama_prodi']) ?> · Angkatan <?= e($profil['angkatan']) ?></p>
        <p class="muted">Dosen wali: <?= e($profil['dosen_wali'] ?? '-') ?></p>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card c-blue">
        <div class="stat-icon"><?= icon('award', 22) ?></div>
        <div><div class="stat-value"><?= $ipk ? angka($ipk['ipk']) : '-' ?></div><div class="stat-label">IPK</div></div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-icon"><?= icon('book', 22) ?></div>
        <div><div class="stat-value"><?= e($ipk['total_sks'] ?? 0) ?></div><div class="stat-label">SKS lulus (bernilai)</div></div>
    </div>
    <a class="stat-card c-amber" href="<?= e(url('mahasiswa/krs')) ?>">
        <div class="stat-icon"><?= icon('clipboard', 22) ?></div>
        <div><div class="stat-value"><?= e($sksSemester) ?></div><div class="stat-label">SKS diambil semester ini</div></div>
    </a>
    <div class="stat-card c-purple">
        <div class="stat-icon"><?= icon('check', 22) ?></div>
        <div>
            <div class="stat-value"><?= e($statusKrs['Disetujui'] ?? 0) ?>/<?= count($krs) ?></div>
            <div class="stat-label">Mata kuliah KRS disetujui</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <section class="card">
        <div class="card-head"><h2>Jadwal Kuliah <?= $ta ? e($ta['tahun'] . ' ' . $ta['semester']) : '' ?></h2></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Hari</th><th>Jam</th><th>Mata Kuliah</th><th>Ruang</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($krs as $r): ?>
                    <tr>
                        <td><?= e($r['hari']) ?></td>
                        <td><?= jam($r['jam_mulai']) ?>–<?= jam($r['jam_selesai']) ?></td>
                        <td><?= e($r['nama_mk']) ?></td>
                        <td><?= e($r['kode_ruangan'] ?? '-') ?></td>
                        <td><span class="badge <?= badge_class($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$krs): ?><tr><td colspan="5" class="empty">Belum ada KRS. <a href="<?= e(url('mahasiswa/krs')) ?>">Isi KRS sekarang</a>.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <section class="card">
        <div class="card-head"><h2>Riwayat IPS</h2></div>
        <?php $maxIps = 4; ?>
        <div class="bars">
            <?php foreach ($semester as $s): ?>
                <div class="bar-row">
                    <span class="bar-label"><?= e($s['tahun']) ?> <?= e($s['semester']) ?></span>
                    <span class="bar-track"><span class="bar-fill" style="width: <?= $s['ips'] !== null ? round($s['ips'] / $maxIps * 100) : 0 ?>%"></span></span>
                    <span class="bar-value"><?= $s['ips'] !== null ? angka($s['ips']) : '<small class="muted">berjalan</small>' ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (!$semester): ?><p class="empty">Belum ada riwayat semester.</p><?php endif; ?>
        </div>
        <a class="btn btn-sm btn-ghost" href="<?= e(url('mahasiswa/khs')) ?>"><?= icon('file', 15) ?><span>Lihat KHS</span></a>
    </section>
</div>
