<section class="card">
    <div class="card-head">
        <h2>Mahasiswa Bimbingan · KRS <?= $ta ? e($ta['tahun'] . ' ' . $ta['semester']) : '' ?></h2>
        <span class="muted small"><?= count($bimbingan) ?> mahasiswa</span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>NPM</th><th>Nama</th><th>Angkatan</th><th>Prodi</th><th class="num">IPK</th><th class="num">MK</th><th class="num">SKS</th><th>Status KRS</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($bimbingan as $i => $b): ?>
                <?php
                $status = match (true) {
                    (int) $b['jumlah_kelas'] === 0 => ['Belum mengisi', 'badge-muted'],
                    (int) $b['jumlah_diajukan'] > 0 => ['Menunggu persetujuan', 'badge-warning'],
                    default => ['Disetujui', 'badge-success'],
                };
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code><?= e($b['npm']) ?></code></td>
                    <td><?= e($b['nama_mahasiswa']) ?></td>
                    <td><?= e($b['angkatan']) ?></td>
                    <td><?= e($b['nama_prodi']) ?></td>
                    <td class="num"><?= angka($b['ipk']) ?></td>
                    <td class="num"><?= e($b['jumlah_kelas']) ?></td>
                    <td class="num"><?= e($b['total_sks']) ?></td>
                    <td><span class="badge <?= $status[1] ?>"><?= $status[0] ?></span></td>
                    <td class="actions"><a class="btn btn-sm btn-ghost" href="<?= e(url('dosen/perwalian', ['action' => 'detail', 'id' => $b['id_mahasiswa']])) ?>"><?= icon('eye', 15) ?><span>Periksa KRS</span></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$bimbingan): ?><tr><td colspan="10" class="empty">Tidak ada mahasiswa bimbingan.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
