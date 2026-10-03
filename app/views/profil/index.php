<div class="grid-2">
    <section class="card">
        <div class="card-head"><h2>Informasi Akun</h2></div>
        <dl class="kv">
            <dt>Nama</dt><dd><?= e($user['nama']) ?></dd>
            <dt>Username</dt><dd><code><?= e($user['username']) ?></code></dd>
            <dt>Peran</dt><dd><?= e(ucfirst($user['role'])) ?></dd>
        </dl>
    </section>
    <section class="card">
        <div class="card-head"><h2>Ganti Password</h2></div>
        <form method="post" action="<?= e(url('profil', ['action' => 'password'])) ?>">
            <?= csrf_field() ?>
            <label class="field">
                <span>Password lama</span>
                <input class="<?= has_error('password_lama') ?>" type="password" name="password_lama" required>
                <?= error_for('password_lama') ?>
            </label>
            <label class="field">
                <span>Password baru (min. 6 karakter)</span>
                <input class="<?= has_error('password_baru') ?>" type="password" name="password_baru" minlength="6" required>
                <?= error_for('password_baru') ?>
            </label>
            <label class="field">
                <span>Ulangi password baru</span>
                <input class="<?= has_error('konfirmasi') ?>" type="password" name="konfirmasi" required>
                <?= error_for('konfirmasi') ?>
            </label>
            <button class="btn btn-primary" type="submit"><?= icon('key', 16) ?><span>Simpan Password</span></button>
        </form>
    </section>
</div>
