<div class="stat-grid">
    <div class="stat-card c-blue">
        <div class="stat-icon"><?= icon('clipboard', 22) ?></div>
        <div><div class="stat-value"><?= count($kelas) ?></div><div class="stat-label">Kelas diampu semester ini</div></div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-icon"><?= icon('users', 22) ?></div>
        <div><div class="stat-value"><?= array_sum(array_column($kelas, 'jumlah_peserta')) ?></div><div class="stat-label">Total peserta kelas</div></div>
    </div>
    <div class="stat-card c-amber">
        <div class="stat-icon"><?= icon('user', 22) ?></div>
        <div><div class="stat-value"><?= count($bimbingan) ?></div><div class="stat-label">Mahasiswa bimbingan (wali)</div></div>
    </div>
    <a class="stat-card c-purple" href="<?= e(url('dosen/perwalian')) ?>">
        <div class="stat-icon"><?= icon('file', 22) ?></div>
        <div><div class="stat-value"><?= e($menunggu) ?></div><div class="stat-label">Mata kuliah KRS menunggu persetujuan</div></div>
    </a>
</div>

<section class="card">
    <div class="card-head">
        <h2>Jadwal Mengajar <?= $ta ? e($ta['tahun'] . ' ' . $ta['semester']) : '' ?></h2>
        <a class="btn btn-sm btn-ghost" href="<?= e(url('dosen/kelas')) ?>">Semua kelas</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Hari</th><th>Jam</th><th>Mata Kuliah</th><th>Kelas</th><th>Ruang</th><th class="num">Peserta</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($kelas as $k): ?>
                <tr>
                    <td><?= e($k['hari']) ?></td>
                    <td><?= jam($k['jam_mulai']) ?>–<?= jam($k['jam_selesai']) ?></td>
                    <td><strong><?= e($k['kode_mk']) ?></strong> <?= e($k['nama_mk']) ?></td>
                    <td><?= e($k['nama_kelas']) ?></td>
                    <td><?= e($k['kode_ruangan'] ?? '-') ?></td>
                    <td class="num"><?= e($k['jumlah_peserta']) ?>/<?= e($k['kuota']) ?></td>
                    <td class="actions"><a class="btn btn-sm btn-primary" href="<?= e(url('dosen/kelas', ['action' => 'nilai', 'id' => $k['id_kelas']])) ?>"><?= icon('edit', 15) ?><span>Input nilai</span></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$kelas): ?><tr><td colspan="7" class="empty">Tidak ada kelas pada semester aktif.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
