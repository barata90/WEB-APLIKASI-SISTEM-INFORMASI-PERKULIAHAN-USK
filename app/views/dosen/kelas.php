<form class="toolbar filter" method="get">
    <input type="hidden" name="page" value="dosen/kelas">
    <select name="ta" onchange="this.form.submit()">
        <?= select_options($taList, 'id_ta', 'label', $idTa, 'Semua tahun akademik') ?>
    </select>
    <span class="muted"><?= count($rows) ?> kelas</span>
</form>
<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>TA</th><th>Hari</th><th>Jam</th><th>Mata Kuliah</th><th class="num">SKS</th><th>Kelas</th><th>Ruang</th><th class="num">Peserta</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $k): ?>
                <tr>
                    <td class="nowrap"><?= e($k['tahun'] . ' ' . $k['semester']) ?></td>
                    <td><?= e($k['hari']) ?></td>
                    <td class="nowrap"><?= jam($k['jam_mulai']) ?>–<?= jam($k['jam_selesai']) ?></td>
                    <td><code><?= e($k['kode_mk']) ?></code> <?= e($k['nama_mk']) ?></td>
                    <td class="num"><?= e($k['sks']) ?></td>
                    <td><?= e($k['nama_kelas']) ?></td>
                    <td><?= e($k['kode_ruangan'] ?? '-') ?></td>
                    <td class="num"><?= e($k['jumlah_peserta']) ?>/<?= e($k['kuota']) ?></td>
                    <td class="actions"><a class="btn btn-sm btn-primary" href="<?= e(url('dosen/kelas', ['action' => 'nilai', 'id' => $k['id_kelas']])) ?>"><?= icon('edit', 15) ?><span>Nilai</span></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="9" class="empty">Tidak ada kelas.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
