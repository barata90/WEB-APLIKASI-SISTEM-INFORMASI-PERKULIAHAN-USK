<form class="toolbar filter" method="get">
    <input type="hidden" name="page" value="admin/mata-kuliah">
    <div class="search">
        <?= icon('search', 16) ?>
        <input type="search" name="cari" value="<?= e($cari) ?>" placeholder="Cari kode atau nama mata kuliah">
    </div>
    <select name="prodi" onchange="this.form.submit()">
        <?= select_options($prodi, 'id_prodi', 'label', $idProdi, 'Semua program studi') ?>
    </select>
    <button class="btn btn-ghost" type="submit">Terapkan</button>
    <span class="spacer"></span>
    <a class="btn btn-primary" href="<?= e(url('admin/mata-kuliah', ['action' => 'create'])) ?>"><?= icon('plus', 16) ?><span>Tambah Mata Kuliah</span></a>
</form>
<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>Kode</th><th>Nama Mata Kuliah</th><th class="num">SKS</th><th class="num">Semester</th><th>Jenis</th><th>Program Studi</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code><?= e($r['kode_mk']) ?></code></td>
                    <td><?= e($r['nama_mk']) ?></td>
                    <td class="num"><?= e($r['sks']) ?></td>
                    <td class="num"><?= e($r['semester_paket']) ?></td>
                    <td><span class="badge <?= $r['jenis'] === 'Wajib' ? 'badge-info' : 'badge-muted' ?>"><?= e($r['jenis']) ?></span></td>
                    <td><?= e($r['jenjang'] . ' ' . $r['nama_prodi']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/mata-kuliah', ['action' => 'edit', 'id' => $r['id_mk']])) ?>"><?= icon('edit', 15) ?><span>Ubah</span></a>
                        <?= delete_button('admin/mata-kuliah', (int) $r['id_mk'], 'Hapus', 'Hapus mata kuliah ' . $r['nama_mk'] . '?') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="8" class="empty">Tidak ada mata kuliah yang cocok.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted small">Menampilkan <?= count($rows) ?> mata kuliah, total <?= array_sum(array_column($rows, 'sks')) ?> SKS.</p>
</section>
