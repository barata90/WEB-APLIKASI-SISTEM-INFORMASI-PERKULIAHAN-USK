<form class="toolbar filter" method="get">
    <input type="hidden" name="page" value="admin/mahasiswa">
    <div class="search">
        <?= icon('search', 16) ?>
        <input type="search" name="cari" value="<?= e($cari) ?>" placeholder="Cari NPM atau nama">
    </div>
    <select name="prodi">
        <?= select_options($prodi, 'id_prodi', 'label', $idProdi, 'Semua prodi') ?>
    </select>
    <select name="angkatan">
        <option value="">Semua angkatan</option>
        <?php foreach ($angkatanList as $a): ?>
            <option <?= (string) $angkatan === (string) $a ? 'selected' : '' ?>><?= e($a) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-ghost" type="submit">Terapkan</button>
    <span class="spacer"></span>
    <a class="btn btn-primary" href="<?= e(url('admin/mahasiswa', ['action' => 'create'])) ?>"><?= icon('plus', 16) ?><span>Tambah Mahasiswa</span></a>
</form>
<section class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>NPM</th><th>Nama Mahasiswa</th><th>L/P</th><th>Angkatan</th><th>Program Studi</th><th>Dosen Wali</th><th class="num">SKS</th><th class="num">IPK</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code><?= e($r['npm']) ?></code></td>
                    <td><?= e($r['nama_mahasiswa']) ?></td>
                    <td><?= e($r['jenis_kelamin']) ?></td>
                    <td><?= e($r['angkatan']) ?></td>
                    <td><?= e($r['nama_prodi']) ?></td>
                    <td><?= e($r['dosen_wali'] ?? '-') ?></td>
                    <td class="num"><?= e($r['total_sks'] ?? 0) ?></td>
                    <td class="num"><?= angka($r['ipk']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/mahasiswa', ['action' => 'edit', 'id' => $r['id_mahasiswa']])) ?>"><?= icon('edit', 15) ?><span>Ubah</span></a>
                        <?= delete_button('admin/mahasiswa', (int) $r['id_mahasiswa'], 'Hapus', 'Hapus mahasiswa ' . $r['nama_mahasiswa'] . '?') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="10" class="empty">Tidak ada mahasiswa yang cocok dengan filter.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted small">Menampilkan <?= count($rows) ?> mahasiswa. IPK dihitung dari view <code>v_ipk</code>.</p>
</section>
