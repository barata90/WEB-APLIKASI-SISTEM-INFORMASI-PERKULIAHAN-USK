<div class="toolbar">
    <span class="muted"><?= count($rows) ?> ruangan</span>
    <a class="btn btn-primary" href="<?= e(url('admin/ruangan', ['action' => 'create'])) ?>"><?= icon('plus', 16) ?><span>Tambah Ruangan</span></a>
</div>
<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>Kode</th><th>Nama Ruangan</th><th>Gedung</th><th class="num">Kapasitas</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code><?= e($r['kode_ruangan']) ?></code></td>
                    <td><?= e($r['nama_ruangan']) ?></td>
                    <td><?= e($r['gedung'] ?? '-') ?></td>
                    <td class="num"><?= e($r['kapasitas']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/ruangan', ['action' => 'edit', 'id' => $r['id_ruangan']])) ?>"><?= icon('edit', 15) ?><span>Ubah</span></a>
                        <?= delete_button('admin/ruangan', (int) $r['id_ruangan']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
