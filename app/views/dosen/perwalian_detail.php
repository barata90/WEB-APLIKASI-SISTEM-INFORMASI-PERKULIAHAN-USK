<?php
$sks = array_sum(array_map(fn($r) => $r['status'] !== 'Ditolak' ? $r['sks'] : 0, $rows));
$diajukan = count(array_filter($rows, fn($r) => $r['status'] === 'Diajukan'));
?>
<section class="card info-strip">
    <div><small class="muted">NPM</small><strong><?= e($mhs['npm']) ?></strong></div>
    <div><small class="muted">Nama</small><strong><?= e($mhs['nama_mahasiswa']) ?></strong></div>
    <div><small class="muted">Prodi / Angkatan</small><strong><?= e($mhs['nama_prodi']) ?> / <?= e($mhs['angkatan']) ?></strong></div>
    <div><small class="muted">IPK</small><strong><?= $ipk ? angka($ipk['ipk']) : '-' ?></strong></div>
    <div><small class="muted">Total SKS diambil</small><strong><?= $sks ?> SKS</strong></div>
</section>
<section class="card">
    <div class="card-head"><h2>KRS <?= e($ta['tahun'] . ' ' . $ta['semester']) ?></h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Kode</th><th>Mata Kuliah</th><th class="num">SKS</th><th>Kelas</th><th>Jadwal</th><th>Dosen</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><code><?= e($r['kode_mk']) ?></code></td>
                    <td><?= e($r['nama_mk']) ?></td>
                    <td class="num"><?= e($r['sks']) ?></td>
                    <td><?= e($r['nama_kelas']) ?></td>
                    <td class="nowrap"><?= e($r['hari']) ?> <?= jam($r['jam_mulai']) ?>–<?= jam($r['jam_selesai']) ?></td>
                    <td><?= e($r['nama_dosen']) ?></td>
                    <td><span class="badge <?= badge_class($r['status']) ?>"><?= e($r['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="7" class="empty">Mahasiswa belum mengisi KRS.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="form-actions">
        <a class="btn btn-ghost" href="<?= e(url('dosen/perwalian')) ?>">Kembali</a>
        <?php if ($diajukan): ?>
            <form class="inline" method="post" action="<?= e(url('dosen/perwalian', ['action' => 'tolak', 'id' => $mhs['id_mahasiswa']])) ?>" onsubmit="return confirm('Tolak semua mata kuliah yang diajukan?')">
                <?= csrf_field() ?><button class="btn btn-danger-ghost" type="submit"><?= icon('x', 16) ?><span>Tolak</span></button>
            </form>
            <form class="inline" method="post" action="<?= e(url('dosen/perwalian', ['action' => 'setujui', 'id' => $mhs['id_mahasiswa']])) ?>">
                <?= csrf_field() ?><button class="btn btn-primary" type="submit"><?= icon('check', 16) ?><span>Setujui <?= $diajukan ?> Mata Kuliah</span></button>
            </form>
        <?php endif; ?>
    </div>
</section>
