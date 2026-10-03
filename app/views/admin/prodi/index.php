<div class="toolbar">
    <span class="muted"><?= count($rows) ?> program studi</span>
    <a class="btn btn-primary" href="<?= e(url('admin/prodi', ['action' => 'create'])) ?>"><?= icon('plus', 16) ?><span>Tambah Prodi</span></a>
</div>
<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>Kode</th><th>Program Studi</th><th>Jenjang</th><th>Fakultas</th><th class="num">Dosen</th><th class="num">Mahasiswa</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code><?= e($r['kode_prodi']) ?></code></td>
                    <td><?= e($r['nama_prodi']) ?></td>
                    <td><?= e($r['jenjang']) ?></td>
                    <td><?= e($r['kode_fakultas']) ?></td>
                    <td class="num"><?= e($r['jumlah_dosen']) ?></td>
                    <td class="num"><?= e($r['jumlah_mahasiswa']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/prodi', ['action' => 'edit', 'id' => $r['id_prodi']])) ?>"><?= icon('edit', 15) ?><span>Ubah</span></a>
                        <?= delete_button('admin/prodi', (int) $r['id_prodi']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
