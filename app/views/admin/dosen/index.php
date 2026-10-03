<form class="toolbar filter" method="get">
    <input type="hidden" name="page" value="admin/dosen">
    <div class="search">
        <?= icon('search', 16) ?>
        <input type="search" name="cari" value="<?= e($cari) ?>" placeholder="Cari NIDN atau nama dosen">
    </div>
    <button class="btn btn-ghost" type="submit">Cari</button>
    <span class="spacer"></span>
    <a class="btn btn-primary" href="<?= e(url('admin/dosen', ['action' => 'create'])) ?>"><?= icon('plus', 16) ?><span>Tambah Dosen</span></a>
</form>
<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>NIDN</th><th>Nama Dosen</th><th>Homebase</th><th>Email</th><th>No. HP</th><th class="num">Bimbingan</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code><?= e($r['nidn']) ?></code></td>
                    <td><?= e($r['nama_dosen']) ?></td>
                    <td><?= e($r['nama_prodi']) ?></td>
                    <td><?= e($r['email'] ?? '-') ?></td>
                    <td><?= e($r['no_hp'] ?? '-') ?></td>
                    <td class="num"><?= e($r['jumlah_bimbingan']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/dosen', ['action' => 'edit', 'id' => $r['id_dosen']])) ?>"><?= icon('edit', 15) ?><span>Ubah</span></a>
                        <?= delete_button('admin/dosen', (int) $r['id_dosen'], 'Hapus', 'Hapus dosen ' . $r['nama_dosen'] . ' beserta akunnya?') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="8" class="empty">Tidak ada data dosen.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
