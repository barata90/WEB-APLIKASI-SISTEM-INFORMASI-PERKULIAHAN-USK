<div class="login-wrap">
    <section class="login-hero">
        <div class="login-hero-inner">
            <img src="<?= e(asset('img/logo.svg')) ?>" alt="" width="64" height="64">
            <h1><?= e(config('app_name')) ?></h1>
            <p class="lead"><?= e(config('app_subtitle')) ?></p>
            <ul class="hero-points">
                <li><?= icon('check', 16) ?> Administrator mengelola data master, kelas, dan jadwal</li>
                <li><?= icon('check', 16) ?> Dosen menyetujui KRS dan menginput nilai</li>
                <li><?= icon('check', 16) ?> Mahasiswa mengisi KRS, melihat KHS dan transkrip</li>
            </ul>
            <p class="hero-note">Proyek UTS Mata Kuliah Manajemen dan Pemodelan Data · PHP + MySQL/MariaDB</p>
        </div>
    </section>
    <section class="login-panel">
        <form class="login-card" method="post" action="<?= e(url('login', ['action' => 'login'])) ?>" autocomplete="off">
            <?= csrf_field() ?>
            <h2>Masuk ke Sistem</h2>
            <p class="muted">Gunakan username dan password yang terdaftar.</p>
            <?php foreach (['success', 'error'] as $type): ?>
                <?php if ($msg = flash($type)): ?>
                    <div class="alert alert-<?= $type ?>"><?= icon($type === 'success' ? 'check' : 'x', 16) ?><span><?= e($msg) ?></span></div>
                <?php endif; ?>
            <?php endforeach; ?>
            <label class="field">
                <span>Username</span>
                <input type="text" name="username" value="<?= e(old('username')) ?>" placeholder="admin / NIDN / NPM" required autofocus>
            </label>
            <label class="field">
                <span>Password</span>
                <input type="password" name="password" placeholder="••••••••" required>
            </label>
            <button class="btn btn-primary btn-block" type="submit">Masuk</button>
            <details class="demo-accounts">
                <summary>Akun demo</summary>
                <table class="table table-compact">
                    <tr><th>Peran</th><th>Username</th><th>Password</th></tr>
                    <tr><td>Admin</td><td><code>admin</code></td><td><code>admin123</code></td></tr>
                    <tr><td>Dosen</td><td><code>0011028108</code></td><td><code>dosen123</code></td></tr>
                    <tr><td>Mahasiswa</td><td><code>2508107010001</code></td><td><code>mhs123</code></td></tr>
                </table>
            </details>
        </form>
    </section>
</div>
