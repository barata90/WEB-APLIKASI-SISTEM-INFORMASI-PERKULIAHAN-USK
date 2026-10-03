<?php $isEdit = $row !== null; ?>
<section class="card form-card">
    <form method="post" action="<?= e(url('admin/mahasiswa', $isEdit ? ['action' => 'update', 'id' => $row['id_mahasiswa']] : ['action' => 'store'])) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <label class="field">
                <span>NPM (13 digit) *</span>
                <input class="<?= has_error('npm') ?>" type="text" name="npm" maxlength="13" inputmode="numeric" value="<?= e(old('npm', $row['npm'] ?? '')) ?>" required>
                <?= error_for('npm') ?>
            </label>
            <label class="field span-2">
                <span>Nama Lengkap *</span>
                <input class="<?= has_error('nama_mahasiswa') ?>" type="text" name="nama_mahasiswa" maxlength="100" value="<?= e(old('nama_mahasiswa', $row['nama_mahasiswa'] ?? '')) ?>" required>
                <?= error_for('nama_mahasiswa') ?>
            </label>
            <label class="field">
                <span>Jenis Kelamin *</span>
                <select class="<?= has_error('jenis_kelamin') ?>" name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="L" <?= old('jenis_kelamin', $row['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= old('jenis_kelamin', $row['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
                <?= error_for('jenis_kelamin') ?>
            </label>
            <label class="field">
                <span>Tanggal Lahir</span>
                <input class="<?= has_error('tanggal_lahir') ?>" type="date" name="tanggal_lahir" value="<?= e(old('tanggal_lahir', $row['tanggal_lahir'] ?? '')) ?>">
                <?= error_for('tanggal_lahir') ?>
            </label>
            <label class="field">
                <span>Email</span>
                <input class="<?= has_error('email') ?>" type="email" name="email" maxlength="100" value="<?= e(old('email', $row['email'] ?? '')) ?>">
                <?= error_for('email') ?>
            </label>
            <label class="field">
                <span>Angkatan *</span>
                <input class="<?= has_error('angkatan') ?>" type="number" name="angkatan" min="2000" max="2100" value="<?= e(old('angkatan', $row['angkatan'] ?? date('Y'))) ?>" required>
                <?= error_for('angkatan') ?>
            </label>
            <label class="field">
                <span>Program Studi *</span>
                <select class="<?= has_error('id_prodi') ?>" name="id_prodi" required>
                    <?= select_options($prodi, 'id_prodi', 'label', old('id_prodi', $row['id_prodi'] ?? '')) ?>
                </select>
                <?= error_for('id_prodi') ?>
            </label>
            <label class="field">
                <span>Dosen Wali</span>
                <select class="<?= has_error('id_dosen_wali') ?>" name="id_dosen_wali">
                    <?= select_options($dosen, 'id_dosen', 'nama_dosen', old('id_dosen_wali', $row['id_dosen_wali'] ?? ''), '-- Belum ditentukan --') ?>
                </select>
                <?= error_for('id_dosen_wali') ?>
            </label>
            <?php if (!$isEdit): ?>
                <label class="field">
                    <span>Password Awal Akun *</span>
                    <input class="<?= has_error('password') ?>" type="text" name="password" minlength="6" value="mhs123" required>
                    <small class="muted">Username login = NPM</small>
                    <?= error_for('password') ?>
                </label>
            <?php endif; ?>
        </div>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= e(url('admin/mahasiswa')) ?>">Batal</a>
            <button class="btn btn-primary" type="submit"><?= icon('save', 16) ?><span>Simpan</span></button>
        </div>
    </form>
</section>
