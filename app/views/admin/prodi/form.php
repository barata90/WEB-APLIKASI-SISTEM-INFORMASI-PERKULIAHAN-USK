<?php $isEdit = $row !== null; ?>
<section class="card form-card">
    <form method="post" action="<?= e(url('admin/prodi', $isEdit ? ['action' => 'update', 'id' => $row['id_prodi']] : ['action' => 'store'])) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <label class="field">
                <span>Kode Prodi *</span>
                <input class="<?= has_error('kode_prodi') ?>" type="text" name="kode_prodi" maxlength="10" value="<?= e(old('kode_prodi', $row['kode_prodi'] ?? '')) ?>" required>
                <?= error_for('kode_prodi') ?>
            </label>
            <label class="field span-2">
                <span>Nama Program Studi *</span>
                <input class="<?= has_error('nama_prodi') ?>" type="text" name="nama_prodi" maxlength="100" value="<?= e(old('nama_prodi', $row['nama_prodi'] ?? '')) ?>" required>
                <?= error_for('nama_prodi') ?>
            </label>
            <label class="field">
                <span>Jenjang *</span>
                <select name="jenjang">
                    <?php foreach (['D3', 'S1', 'S2', 'S3'] as $j): ?>
                        <option <?= old('jenjang', $row['jenjang'] ?? 'S1') === $j ? 'selected' : '' ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field span-2">
                <span>Fakultas *</span>
                <select class="<?= has_error('id_fakultas') ?>" name="id_fakultas" required>
                    <?= select_options($fakultas, 'id_fakultas', fn($f) => $f['kode_fakultas'] . ' - ' . $f['nama_fakultas'], old('id_fakultas', $row['id_fakultas'] ?? '')) ?>
                </select>
                <?= error_for('id_fakultas') ?>
            </label>
        </div>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= e(url('admin/prodi')) ?>">Batal</a>
            <button class="btn btn-primary" type="submit"><?= icon('save', 16) ?><span>Simpan</span></button>
        </div>
    </form>
</section>
