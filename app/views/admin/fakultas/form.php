<?php $isEdit = $row !== null; ?>
<section class="card form-card">
    <form method="post" action="<?= e(url('admin/fakultas', $isEdit ? ['action' => 'update', 'id' => $row['id_fakultas']] : ['action' => 'store'])) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <label class="field">
                <span>Kode Fakultas *</span>
                <input class="<?= has_error('kode_fakultas') ?>" type="text" name="kode_fakultas" maxlength="10" value="<?= e(old('kode_fakultas', $row['kode_fakultas'] ?? '')) ?>" placeholder="mis. FMIPA" required>
                <?= error_for('kode_fakultas') ?>
            </label>
            <label class="field span-2">
                <span>Nama Fakultas *</span>
                <input class="<?= has_error('nama_fakultas') ?>" type="text" name="nama_fakultas" maxlength="100" value="<?= e(old('nama_fakultas', $row['nama_fakultas'] ?? '')) ?>" required>
                <?= error_for('nama_fakultas') ?>
            </label>
        </div>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= e(url('admin/fakultas')) ?>">Batal</a>
            <button class="btn btn-primary" type="submit"><?= icon('save', 16) ?><span>Simpan</span></button>
        </div>
    </form>
</section>
