<?php $isEdit = $row !== null; ?>
<section class="card form-card">
    <form method="post" action="<?= e(url('admin/tahun-akademik', $isEdit ? ['action' => 'update', 'id' => $row['id_ta']] : ['action' => 'store'])) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <label class="field">
                <span>Tahun Akademik *</span>
                <input class="<?= has_error('tahun') ?>" type="text" name="tahun" maxlength="9" placeholder="2026/2027" value="<?= e(old('tahun', $row['tahun'] ?? '')) ?>" required>
                <?= error_for('tahun') ?>
            </label>
            <label class="field">
                <span>Semester *</span>
                <select name="semester">
                    <?php foreach (['Ganjil', 'Genap'] as $s): ?>
                        <option <?= old('semester', $row['semester'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field checkbox">
                <input type="checkbox" name="is_aktif" value="1" <?= old('is_aktif', $row['is_aktif'] ?? '') ? 'checked' : '' ?>>
                <span>Jadikan tahun akademik aktif</span>
            </label>
        </div>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= e(url('admin/tahun-akademik')) ?>">Batal</a>
            <button class="btn btn-primary" type="submit"><?= icon('save', 16) ?><span>Simpan</span></button>
        </div>
    </form>
</section>
