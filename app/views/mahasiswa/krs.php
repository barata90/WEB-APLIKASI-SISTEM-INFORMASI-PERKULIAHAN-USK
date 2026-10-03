<section class="card info-strip">
    <div><small class="muted">Mahasiswa</small><strong><?= e($profil['nama_mahasiswa']) ?></strong></div>
    <div><small class="muted">NPM</small><strong><?= e($profil['npm']) ?></strong></div>
    <div><small class="muted">Dosen wali</small><strong><?= e($profil['dosen_wali'] ?? '-') ?></strong></div>
    <div><small class="muted">Semester</small><strong><?= $ta ? e($ta['tahun'] . ' ' . $ta['semester']) : '-' ?></strong></div>
    <div>
        <small class="muted">Total SKS</small>
        <strong><?= e($totalSks) ?> / <?= e($maksSks) ?></strong>
        <span class="progress"><span style="width: <?= min(100, round($totalSks / $maksSks * 100)) ?>%"></span></span>
    </div>
</section>

<?php if (!$ta): ?>
    <div class="alert alert-error">Belum ada tahun akademik aktif. Pengisian KRS belum dibuka.</div>
<?php else: ?>
<section class="card">
    <div class="card-head"><h2>KRS Saya</h2><span class="muted small">Mata kuliah berstatus <em>Diajukan</em> masih dapat dibatalkan</span></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Kode</th><th>Mata Kuliah</th><th class="num">SKS</th><th>Kelas</th><th>Jadwal</th><th>Ruang</th><th>Dosen</th><th>Status</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($krs as $r): ?>
                <tr>
                    <td><code><?= e($r['kode_mk']) ?></code></td>
                    <td><?= e($r['nama_mk']) ?></td>
                    <td class="num"><?= e($r['sks']) ?></td>
                    <td><?= e($r['nama_kelas']) ?></td>
                    <td class="nowrap"><?= e($r['hari']) ?> <?= jam($r['jam_mulai']) ?>–<?= jam($r['jam_selesai']) ?></td>
                    <td><?= e($r['kode_ruangan'] ?? '-') ?></td>
                    <td><?= e($r['nama_dosen']) ?></td>
                    <td><span class="badge <?= badge_class($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td class="actions">
                        <?php if ($r['status'] !== 'Disetujui'): ?>
                            <form class="inline" method="post" action="<?= e(url('mahasiswa/krs', ['action' => 'batal', 'id' => $r['id_krs']])) ?>" onsubmit="return confirm('Batalkan mata kuliah ini dari KRS?')">
                                <?= csrf_field() ?><button class="btn btn-sm btn-danger-ghost" type="submit"><?= icon('x', 15) ?><span>Batal</span></button>
                            </form>
                        <?php else: ?>
                            <span class="muted small">terkunci</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$krs): ?><tr><td colspan="9" class="empty">Belum ada mata kuliah di KRS. Pilih dari daftar kelas di bawah.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card">
    <div class="card-head"><h2>Kelas yang Ditawarkan</h2><span class="muted small">Program studi <?= e($profil['nama_prodi']) ?>, <?= e($ta['tahun'] . ' ' . $ta['semester']) ?></span></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Kode</th><th>Mata Kuliah</th><th class="num">SKS</th><th class="num">Smt</th><th>Kelas</th><th>Jadwal</th><th>Dosen</th><th class="num">Terisi</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($tersedia as $k): ?>
                <?php $penuh = (int) $k['jumlah_peserta'] >= (int) $k['kuota']; ?>
                <tr>
                    <td><code><?= e($k['kode_mk']) ?></code></td>
                    <td><?= e($k['nama_mk']) ?></td>
                    <td class="num"><?= e($k['sks']) ?></td>
                    <td class="num"><?= e($k['semester_paket']) ?></td>
                    <td><?= e($k['nama_kelas']) ?></td>
                    <td class="nowrap"><?= e($k['hari']) ?> <?= jam($k['jam_mulai']) ?>–<?= jam($k['jam_selesai']) ?></td>
                    <td><?= e($k['nama_dosen']) ?></td>
                    <td class="num"><?= e($k['jumlah_peserta']) ?>/<?= e($k['kuota']) ?></td>
                    <td class="actions">
                        <form class="inline" method="post" action="<?= e(url('mahasiswa/krs', ['action' => 'ambil'])) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id_kelas" value="<?= e($k['id_kelas']) ?>">
                            <button class="btn btn-sm btn-primary" type="submit" <?= $penuh ? 'disabled' : '' ?>><?= icon('plus', 15) ?><span><?= $penuh ? 'Penuh' : 'Ambil' ?></span></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$tersedia): ?><tr><td colspan="9" class="empty">Tidak ada kelas lain yang dapat diambil.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted small">Sistem memeriksa kuota, mata kuliah ganda, bentrok jadwal, dan batas maksimal <?= e($maksSks) ?> SKS sebelum menyimpan.</p>
</section>
<?php endif; ?>
