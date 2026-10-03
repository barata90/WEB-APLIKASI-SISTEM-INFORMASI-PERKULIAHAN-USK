<div class="grid-side">
    <section class="card">
        <form class="toolbar filter" method="get">
            <input type="hidden" name="page" value="admin/pengguna">
            <select name="role" onchange="this.form.submit()">
                <option value="">Semua peran</option>
                <?php foreach (['admin' => 'Administrator', 'dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $role === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <span class="muted"><?= count($rows) ?> akun</span>
        </form>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Username</th><th>Nama</th><th>Peran</th><th>Status</th><th>Login Terakhir</th><th class="actions">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><code><?= e($r['username']) ?></code></td>
                        <td><?= e($r['nama']) ?></td>
                        <td><span class="badge badge-muted"><?= e(ucfirst($r['role'])) ?></span></td>
                        <td><span class="badge <?= badge_class($r['is_aktif'] ? 'Aktif' : 'Nonaktif') ?>"><?= $r['is_aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                        <td class="nowrap"><?= $r['last_login'] ? e(tanggal_indo($r['last_login'], true)) : '<span class="muted">belum pernah</span>' ?></td>
                        <td class="actions">
                            <form class="inline" method="post" action="<?= e(url('admin/pengguna', ['action' => 'reset', 'id' => $r['id_user']])) ?>" onsubmit="return confirm('Reset password menjadi sama dengan username?')">
                                <?= csrf_field() ?><button class="btn btn-sm btn-ghost" type="submit"><?= icon('refresh', 15) ?><span>Reset</span></button>
                            </form>
                            <form class="inline" method="post" action="<?= e(url('admin/pengguna', ['action' => 'toggle', 'id' => $r['id_user']])) ?>">
                                <?= csrf_field() ?><button class="btn btn-sm btn-ghost" type="submit"><?= $r['is_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <section class="card">
        <div class="card-head"><h2>Tambah Administrator</h2></div>
        <form method="post" action="<?= e(url('admin/pengguna', ['action' => 'store'])) ?>">
            <?= csrf_field() ?>
            <label class="field">
                <span>Username</span>
                <input class="<?= has_error('username') ?>" type="text" name="username" maxlength="30" value="<?= e(old('username')) ?>" required>
                <?= error_for('username') ?>
            </label>
            <label class="field">
                <span>Password</span>
                <input class="<?= has_error('password') ?>" type="password" name="password" minlength="6" required>
                <?= error_for('password') ?>
            </label>
            <button class="btn btn-primary btn-block" type="submit"><?= icon('plus', 16) ?><span>Buat Akun</span></button>
        </form>
        <p class="muted small">Akun dosen dan mahasiswa dibuat otomatis saat data dosen/mahasiswa ditambahkan (username = NIDN/NPM). Password disimpan sebagai hash bcrypt.</p>
    </section>
</div>
