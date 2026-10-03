<form class="toolbar filter" method="get">
    <input type="hidden" name="page" value="admin/kelas">
    <select name="ta">
        <?= select_options($taList, 'id_ta', 'label', $idTa, 'Semua tahun akademik') ?>
    </select>
    <select name="prodi">
        <?= select_options($prodi, 'id_prodi', 'label', $idProdi, 'Semua prodi') ?>
    </select>
    <button class="btn btn-ghost" type="submit">Terapkan</button>
    <span class="spacer"></span>
    <a class="btn btn-primary" href="<?= e(url('admin/kelas', ['action' => 'create'])) ?>"><?= icon('plus', 16) ?><span>Tambah Kelas</span></a>
</form>
<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Hari</th><th>Jam</th><th>Mata Kuliah</th><th class="num">SKS</th><th>Kelas</th><th>Dosen Pengampu</th><th>Ruang</th><th>TA</th><th class="num">Peserta</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['hari']) ?></td>
                    <td class="nowrap"><?= jam($r['jam_mulai']) ?>–<?= jam($r['jam_selesai']) ?></td>
                    <td><code><?= e($r['kode_mk']) ?></code> <?= e($r['nama_mk']) ?></td>
                    <td class="num"><?= e($r['sks']) ?></td>
                    <td><?= e($r['nama_kelas']) ?></td>
                    <td><?= e($r['nama_dosen']) ?></td>
                    <td><?= e($r['kode_ruangan'] ?? '-') ?></td>
                    <td class="nowrap"><?= e($r['tahun'] . ' ' . $r['semester']) ?></td>
                    <td class="num"><?= e($r['jumlah_peserta']) ?>/<?= e($r['kuota']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/kelas', ['action' => 'edit', 'id' => $r['id_kelas']])) ?>"><?= icon('edit', 15) ?><span>Ubah</span></a>
                        <?= delete_button('admin/kelas', (int) $r['id_kelas']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="10" class="empty">Belum ada kelas.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted small"><?= count($rows) ?> kelas. Sistem menolak jadwal yang bentrok untuk dosen atau ruangan yang sama.</p>
</section>
