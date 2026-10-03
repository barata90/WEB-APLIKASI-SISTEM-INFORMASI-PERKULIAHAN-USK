<?php $perSemester = []; foreach ($rows as $r) { $perSemester[$r['semester_paket']][] = $r; } ?>
<div class="toolbar no-print">
    <span class="muted">Transkrip sementara berisi mata kuliah yang nilainya sudah lengkap.</span>
    <span class="spacer"></span>
    <button class="btn btn-ghost" type="button" onclick="window.print()"><?= icon('printer', 16) ?><span>Cetak</span></button>
</div>
<section class="card doc">
    <div class="doc-head">
        <h2>TRANSKRIP NILAI SEMENTARA</h2>
        <p><?= e($profil['nama_fakultas']) ?></p>
    </div>
    <dl class="kv kv-2col">
        <dt>Nama</dt><dd><?= e($profil['nama_mahasiswa']) ?></dd>
        <dt>NPM</dt><dd><?= e($profil['npm']) ?></dd>
        <dt>Program Studi</dt><dd><?= e($profil['jenjang'] . ' ' . $profil['nama_prodi']) ?></dd>
        <dt>Angkatan</dt><dd><?= e($profil['angkatan']) ?></dd>
    </dl>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>Kode</th><th>Mata Kuliah</th><th class="num">SKS</th><th>Diambil</th><th class="num">Nilai</th><th>Huruf</th><th class="num">Bobot</th><th class="num">Mutu</th></tr></thead>
            <?php $no = 1; foreach ($perSemester as $smt => $items): ?>
                <tbody>
                <tr class="group-row"><td colspan="9">Semester <?= e($smt) ?></td></tr>
                <?php foreach ($items as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><code><?= e($r['kode_mk']) ?></code></td>
                        <td><?= e($r['nama_mk']) ?></td>
                        <td class="num"><?= e($r['sks']) ?></td>
                        <td class="nowrap"><?= e($r['tahun'] . ' ' . $r['semester']) ?></td>
                        <td class="num"><?= angka($r['nilai_akhir']) ?></td>
                        <td><span class="badge <?= badge_class($r['huruf']) ?>"><?= e($r['huruf']) ?></span></td>
                        <td class="num"><?= angka($r['bobot']) ?></td>
                        <td class="num"><?= angka($r['mutu']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tbody><tr><td colspan="9" class="empty">Belum ada nilai.</td></tr></tbody><?php endif; ?>
            <tfoot>
                <tr><th colspan="3">Total SKS</th><th class="num"><?= e($ipk['total_sks'] ?? 0) ?></th><th colspan="4">Total mutu</th><th class="num"><?= angka(array_sum(array_column($rows, 'mutu'))) ?></th></tr>
                <tr><th colspan="3">Indeks Prestasi Kumulatif</th><th colspan="6"><?= $ipk ? angka($ipk['ipk']) : '-' ?></th></tr>
            </tfoot>
        </table>
    </div>
    <p class="muted small">IPK = Σ(SKS × bobot) / Σ SKS. Skala: <?php foreach ($skala as $s) { echo e($s['huruf']) . ' = ' . angka($s['bobot']) . '; '; } ?></p>
</section>
