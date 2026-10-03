<?php
$sksBernilai = array_sum(array_map(fn($r) => $r['huruf'] !== null ? $r['sks'] : 0, $rows));
$mutu = array_sum(array_map(fn($r) => $r['huruf'] !== null ? $r['mutu'] : 0, $rows));
$belumLengkap = count(array_filter($rows, fn($r) => $r['huruf'] === null));
?>
<form class="toolbar filter no-print" method="get">
    <input type="hidden" name="page" value="mahasiswa/khs">
    <label class="muted" for="ta">Semester</label>
    <select id="ta" name="ta" onchange="this.form.submit()">
        <?= select_options($semester, 'id_ta', fn($s) => $s['tahun'] . ' ' . $s['semester'], $idTa, '') ?>
    </select>
    <span class="spacer"></span>
    <button class="btn btn-ghost" type="button" onclick="window.print()"><?= icon('printer', 16) ?><span>Cetak</span></button>
</form>

<section class="card doc">
    <div class="doc-head">
        <h2>KARTU HASIL STUDI</h2>
        <p><?= $current ? e('Tahun Akademik ' . $current['tahun'] . ' Semester ' . $current['semester']) : '' ?></p>
    </div>
    <dl class="kv kv-2col">
        <dt>Nama</dt><dd><?= e($profil['nama_mahasiswa']) ?></dd>
        <dt>NPM</dt><dd><?= e($profil['npm']) ?></dd>
        <dt>Program Studi</dt><dd><?= e($profil['jenjang'] . ' ' . $profil['nama_prodi']) ?></dd>
        <dt>Dosen Wali</dt><dd><?= e($profil['dosen_wali'] ?? '-') ?></dd>
    </dl>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>Kode</th><th>Mata Kuliah</th><th class="num">SKS</th><th class="num">Tugas</th><th class="num">UTS</th><th class="num">UAS</th><th class="num">Akhir</th><th>Huruf</th><th class="num">Bobot</th><th class="num">SKS × Bobot</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code><?= e($r['kode_mk']) ?></code></td>
                    <td><?= e($r['nama_mk']) ?></td>
                    <td class="num"><?= e($r['sks']) ?></td>
                    <td class="num"><?= angka($r['nilai_tugas']) ?></td>
                    <td class="num"><?= angka($r['nilai_uts']) ?></td>
                    <td class="num"><?= angka($r['nilai_uas']) ?></td>
                    <td class="num"><strong><?= angka($r['nilai_akhir']) ?></strong></td>
                    <td><span class="badge <?= badge_class($r['huruf']) ?>"><?= e($r['huruf'] ?? 'Belum') ?></span></td>
                    <td class="num"><?= angka($r['bobot']) ?></td>
                    <td class="num"><?= angka($r['mutu']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="11" class="empty">Belum ada mata kuliah yang disetujui pada semester ini.</td></tr><?php endif; ?>
            </tbody>
            <tfoot>
                <tr><th colspan="3">Jumlah SKS bernilai / mutu</th><th class="num"><?= $sksBernilai ?></th><th colspan="6"></th><th class="num"><?= angka($mutu) ?></th></tr>
                <tr><th colspan="3">Indeks Prestasi Semester (IPS)</th><th colspan="8"><?php if ($current && $current['ips'] !== null): ?>
                    <?= angka($current['ips']) ?><?= $belumLengkap ? ' <span class="muted small">(sementara: dihitung dari ' . $sksBernilai . ' SKS bernilai, ' . $belumLengkap . ' mata kuliah belum dinilai)</span>' : '' ?>
                <?php else: ?>Belum tersedia (nilai belum lengkap)<?php endif; ?></th></tr>
                <tr><th colspan="3">Indeks Prestasi Kumulatif (IPK)</th><th colspan="8"><?= $ipk ? angka($ipk['ipk']) . ' dari ' . e($ipk['total_sks']) . ' SKS' : '-' ?></th></tr>
            </tfoot>
        </table>
    </div>
</section>
