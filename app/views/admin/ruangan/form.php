<?php $isEdit = $row !== null; ?>
<section class="card form-card">
    <form method="post" action="<?= e(url('admin/ruangan', $isEdit ? ['action' => 'update', 'id' => $row['id_ruangan']] : ['action' => 'store'])) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <label class="field">
                <span>Kode Ruangan *</span>
                <input class="<?= has_error('kode_ruangan') ?>" type="text" name="kode_ruangan" maxlength="15" value="<?= e(old('kode_ruangan', $row['kode_ruangan'] ?? '')) ?>" required>
                <?= error_for('kode_ruangan') ?>
            </label>
            <label class="field span-2">
                <span>Nama Ruangan *</span>
                <input class="<?= has_error('nama_ruangan') ?>" type="text" name="nama_ruangan" maxlength="100" value="<?= e(old('nama_ruangan', $row['nama_ruangan'] ?? '')) ?>" required>
                <?= error_for('nama_ruangan') ?>
            </label>
            <label class="field span-2">
                <span>Gedung</span>
                <input type="text" name="gedung" maxlength="100" value="<?= e(old('gedung', $row['gedung'] ?? '')) ?>">
            </label>
            <label class="field">
                <span>Kapasitas *</span>
                <input class="<?= has_error('kapasitas') ?>" type="number" name="kapasitas" min="1" max="500" value="<?= e(old('kapasitas', $row['kapasitas'] ?? 40)) ?>" required>
                <?= error_for('kapasitas') ?>
            </label>
        </div>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= e(url('admin/ruangan')) ?>">Batal</a>
            <button class="btn btn-primary" type="submit"><?= icon('save', 16) ?><span>Simpan</span></button>
        </div>
    </form>
</section>
