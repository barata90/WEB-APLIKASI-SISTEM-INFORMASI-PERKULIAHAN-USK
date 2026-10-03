<div class="toolbar">
    <span class="muted">Hanya satu tahun akademik yang boleh aktif. KRS dan input nilai mengikuti tahun akademik aktif.</span>
    <a class="btn btn-primary" href="<?= e(url('admin/tahun-akademik', ['action' => 'create'])) ?>"><?= icon('plus', 16) ?><span>Tambah</span></a>
</div>
<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>Tahun Akademik</th><th>Semester</th><th>Status</th><th class="num">Jumlah Kelas</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($r['tahun']) ?></td>
                    <td><?= e($r['semester']) ?></td>
                    <td><?= $r['is_aktif'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-muted">Tidak aktif</span>' ?></td>
                    <td class="num"><?= e($r['jumlah_kelas']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/tahun-akademik', ['action' => 'edit', 'id' => $r['id_ta']])) ?>"><?= icon('edit', 15) ?><span>Ubah</span></a>
                        <?= delete_button('admin/tahun-akademik', (int) $r['id_ta']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
