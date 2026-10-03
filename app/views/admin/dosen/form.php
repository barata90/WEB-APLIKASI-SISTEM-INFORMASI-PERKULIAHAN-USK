<?php $isEdit = $row !== null; ?>
<section class="card form-card">
    <form method="post" action="<?= e(url('admin/dosen', $isEdit ? ['action' => 'update', 'id' => $row['id_dosen']] : ['action' => 'store'])) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <label class="field">
                <span>NIDN (10 digit) *</span>
                <input class="<?= has_error('nidn') ?>" type="text" name="nidn" maxlength="10" inputmode="numeric" value="<?= e(old('nidn', $row['nidn'] ?? '')) ?>" required>
                <?= error_for('nidn') ?>
            </label>
            <label class="field span-2">
                <span>Nama Lengkap dan Gelar *</span>
                <input class="<?= has_error('nama_dosen') ?>" type="text" name="nama_dosen" maxlength="100" value="<?= e(old('nama_dosen', $row['nama_dosen'] ?? '')) ?>" required>
                <?= error_for('nama_dosen') ?>
            </label>
            <label class="field">
                <span>Email</span>
                <input class="<?= has_error('email') ?>" type="email" name="email" maxlength="100" value="<?= e(old('email', $row['email'] ?? '')) ?>">
                <?= error_for('email') ?>
            </label>
            <label class="field">
                <span>No. HP</span>
                <input class="<?= has_error('no_hp') ?>" type="text" name="no_hp" maxlength="20" value="<?= e(old('no_hp', $row['no_hp'] ?? '')) ?>">
                <?= error_for('no_hp') ?>
            </label>
            <label class="field">
                <span>Homebase Prodi *</span>
                <select class="<?= has_error('id_prodi') ?>" name="id_prodi" required>
                    <?= select_options($prodi, 'id_prodi', 'label', old('id_prodi', $row['id_prodi'] ?? '')) ?>
                </select>
                <?= error_for('id_prodi') ?>
            </label>
            <?php if (!$isEdit): ?>
                <label class="field">
                    <span>Password Awal Akun *</span>
                    <input class="<?= has_error('password') ?>" type="text" name="password" minlength="6" value="dosen123" required>
                    <small class="muted">Username login = NIDN</small>
                    <?= error_for('password') ?>
                </label>
            <?php endif; ?>
        </div>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= e(url('admin/dosen')) ?>">Batal</a>
            <button class="btn btn-primary" type="submit"><?= icon('save', 16) ?><span>Simpan</span></button>
        </div>
    </form>
</section>
