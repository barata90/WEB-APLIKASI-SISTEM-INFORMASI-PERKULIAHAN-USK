<?php $isEdit = $row !== null; ?>
<section class="card form-card">
    <form method="post" action="<?= e(url('admin/mata-kuliah', $isEdit ? ['action' => 'update', 'id' => $row['id_mk']] : ['action' => 'store'])) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <label class="field">
                <span>Kode Mata Kuliah *</span>
                <input class="<?= has_error('kode_mk') ?>" type="text" name="kode_mk" maxlength="10" placeholder="mis. INF306" value="<?= e(old('kode_mk', $row['kode_mk'] ?? '')) ?>" required>
                <?= error_for('kode_mk') ?>
            </label>
            <label class="field span-2">
                <span>Nama Mata Kuliah *</span>
                <input class="<?= has_error('nama_mk') ?>" type="text" name="nama_mk" maxlength="100" value="<?= e(old('nama_mk', $row['nama_mk'] ?? '')) ?>" required>
                <?= error_for('nama_mk') ?>
            </label>
            <label class="field">
                <span>SKS *</span>
                <input class="<?= has_error('sks') ?>" type="number" name="sks" min="1" max="6" value="<?= e(old('sks', $row['sks'] ?? 3)) ?>" required>
                <?= error_for('sks') ?>
            </label>
            <label class="field">
                <span>Semester Paket *</span>
                <input class="<?= has_error('semester_paket') ?>" type="number" name="semester_paket" min="1" max="8" value="<?= e(old('semester_paket', $row['semester_paket'] ?? 1)) ?>" required>
                <?= error_for('semester_paket') ?>
            </label>
            <label class="field">
                <span>Jenis *</span>
                <select name="jenis">
                    <?php foreach (['Wajib', 'Pilihan'] as $j): ?>
                        <option <?= old('jenis', $row['jenis'] ?? 'Wajib') === $j ? 'selected' : '' ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field span-3">
                <span>Program Studi *</span>
                <select class="<?= has_error('id_prodi') ?>" name="id_prodi" required>
                    <?= select_options($prodi, 'id_prodi', 'label', old('id_prodi', $row['id_prodi'] ?? '')) ?>
                </select>
                <?= error_for('id_prodi') ?>
            </label>
        </div>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= e(url('admin/mata-kuliah')) ?>">Batal</a>
            <button class="btn btn-primary" type="submit"><?= icon('save', 16) ?><span>Simpan</span></button>
        </div>
    </form>
</section>
