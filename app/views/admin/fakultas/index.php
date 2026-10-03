<div class="toolbar">
    <span class="muted"><?= count($rows) ?> fakultas</span>
    <a class="btn btn-primary" href="<?= e(url('admin/fakultas', ['action' => 'create'])) ?>"><?= icon('plus', 16) ?><span>Tambah Fakultas</span></a>
</div>
<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>Kode</th><th>Nama Fakultas</th><th class="num">Jumlah Prodi</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code><?= e($r['kode_fakultas']) ?></code></td>
                    <td><?= e($r['nama_fakultas']) ?></td>
                    <td class="num"><?= e($r['jumlah_prodi']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/fakultas', ['action' => 'edit', 'id' => $r['id_fakultas']])) ?>"><?= icon('edit', 15) ?><span>Ubah</span></a>
                        <?= delete_button('admin/fakultas', (int) $r['id_fakultas']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="empty">Belum ada data.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
