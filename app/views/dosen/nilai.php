<?php
$lengkap = count(array_filter($peserta, fn($p) => $p['huruf'] !== null));
$val = function (array $p, string $f) use ($oldNilai) {
    if (isset($oldNilai[$p['id_krs']][$f])) {
        return $oldNilai[$p['id_krs']][$f];
    }
    return $p[$f] === null ? '' : rtrim(rtrim($p[$f], '0'), '.');
};
?>
<section class="card info-strip">
    <div><small class="muted">Mata kuliah</small><strong><?= e($kelas['kode_mk'] . ' ' . $kelas['nama_mk']) ?></strong></div>
    <div><small class="muted">Kelas</small><strong><?= e($kelas['nama_kelas']) ?> · <?= e($kelas['sks']) ?> SKS</strong></div>
    <div><small class="muted">Tahun akademik</small><strong><?= e($kelas['tahun'] . ' ' . $kelas['semester']) ?></strong></div>
    <div><small class="muted">Jadwal</small><strong><?= e($kelas['hari']) ?>, <?= jam($kelas['jam_mulai']) ?>–<?= jam($kelas['jam_selesai']) ?></strong></div>
    <div><small class="muted">Nilai lengkap</small><strong><?= $lengkap ?>/<?= count($peserta) ?> mahasiswa</strong></div>
</section>

<form method="post" action="<?= e(url('dosen/kelas', ['action' => 'simpan-nilai', 'id' => $kelas['id_kelas']])) ?>">
    <?= csrf_field() ?>
    <section class="card">
        <div class="card-head">
            <h2>Daftar Peserta &amp; Nilai</h2>
            <span class="muted small">Nilai akhir = 30% Tugas + 30% UTS + 40% UAS</span>
        </div>
        <div class="table-wrap">
            <table class="table table-input">
                <thead><tr><th>No</th><th>NPM</th><th>Nama Mahasiswa</th><th class="num">Tugas</th><th class="num">UTS</th><th class="num">UAS</th><th class="num">Nilai Akhir</th><th>Huruf</th></tr></thead>
                <tbody>
                <?php foreach ($peserta as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><code><?= e($p['npm']) ?></code></td>
                        <td><?= e($p['nama_mahasiswa']) ?></td>
                        <?php foreach (['nilai_tugas', 'nilai_uts', 'nilai_uas'] as $f): ?>
                            <td class="num">
                                <input class="nilai-input<?= has_error("nilai.{$p['id_krs']}.$f") ?>" type="number" step="0.01" min="0" max="100"
                                       name="nilai[<?= e($p['id_krs']) ?>][<?= $f ?>]" value="<?= e($val($p, $f)) ?>" data-field="<?= $f ?>">
                            </td>
                        <?php endforeach; ?>
                        <td class="num"><strong class="nilai-akhir"><?= angka($p['nilai_akhir']) ?></strong></td>
                        <td><span class="badge huruf <?= badge_class($p['huruf']) ?>"><?= e($p['huruf'] ?? '-') ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$peserta): ?><tr><td colspan="8" class="empty">Belum ada peserta dengan KRS disetujui.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= e(url('dosen/kelas')) ?>">Kembali</a>
            <?php if ($peserta): ?><button class="btn btn-primary" type="submit"><?= icon('save', 16) ?><span>Simpan Nilai</span></button><?php endif; ?>
        </div>
    </section>
</form>

<section class="card">
    <div class="card-head"><h2>Skala Konversi Nilai</h2></div>
    <div class="scale" id="skala" data-skala='<?= e(json_encode(array_map(fn($s) => [$s['huruf'], (float) $s['batas_bawah'], (float) $s['batas_atas']], $skala))) ?>'>
        <?php foreach ($skala as $s): ?>
            <span class="scale-item"><span class="badge <?= badge_class($s['huruf']) ?>"><?= e($s['huruf']) ?></span>
            <?= angka($s['batas_bawah'], 0) ?>–<?= $s['batas_atas'] > 100 ? '100' : '&lt;' . angka($s['batas_atas'], 0) ?> · bobot <?= angka($s['bobot']) ?></span>
        <?php endforeach; ?>
    </div>
</section>
