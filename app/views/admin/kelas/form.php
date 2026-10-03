<?php $isEdit = $row !== null; ?>
<section class="card form-card">
    <form method="post" action="<?= e(url('admin/kelas', $isEdit ? ['action' => 'update', 'id' => $row['id_kelas']] : ['action' => 'store'])) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <label class="field span-2">
                <span>Mata Kuliah *</span>
                <select class="<?= has_error('id_mk') ?>" name="id_mk" required>
                    <?= select_options($mk, 'id_mk', 'label', old('id_mk', $row['id_mk'] ?? '')) ?>
                </select>
                <?= error_for('id_mk') ?>
            </label>
            <label class="field">
                <span>Tahun Akademik *</span>
                <select class="<?= has_error('id_ta') ?>" name="id_ta" required>
                    <?= select_options($taList, 'id_ta', 'label', old('id_ta', $row['id_ta'] ?? ($aktif['id_ta'] ?? ''))) ?>
                </select>
                <?= error_for('id_ta') ?>
            </label>
            <label class="field span-2">
                <span>Dosen Pengampu *</span>
                <select class="<?= has_error('id_dosen') ?>" name="id_dosen" required>
                    <?= select_options($dosen, 'id_dosen', 'nama_dosen', old('id_dosen', $row['id_dosen'] ?? '')) ?>
                </select>
                <?= error_for('id_dosen') ?>
            </label>
            <label class="field">
                <span>Nama Kelas *</span>
                <input class="<?= has_error('nama_kelas') ?>" type="text" name="nama_kelas" maxlength="5" value="<?= e(old('nama_kelas', $row['nama_kelas'] ?? '01')) ?>" required>
                <?= error_for('nama_kelas') ?>
            </label>
            <label class="field">
                <span>Hari *</span>
                <select class="<?= has_error('hari') ?>" name="hari" required>
                    <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h): ?>
                        <option <?= old('hari', $row['hari'] ?? '') === $h ? 'selected' : '' ?>><?= $h ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span>Jam Mulai *</span>
                <input class="<?= has_error('jam_mulai') ?>" type="time" name="jam_mulai" value="<?= e(substr(old('jam_mulai', $row['jam_mulai'] ?? '08:00'), 0, 5)) ?>" required>
                <?= error_for('jam_mulai') ?>
            </label>
            <label class="field">
                <span>Jam Selesai *</span>
                <input class="<?= has_error('jam_selesai') ?>" type="time" name="jam_selesai" value="<?= e(substr(old('jam_selesai', $row['jam_selesai'] ?? '09:40'), 0, 5)) ?>" required>
                <?= error_for('jam_selesai') ?>
            </label>
            <label class="field">
                <span>Ruangan</span>
                <select name="id_ruangan">
                    <?= select_options($ruangan, 'id_ruangan', fn($r) => $r['kode_ruangan'] . ' (' . $r['kapasitas'] . ' kursi)', old('id_ruangan', $row['id_ruangan'] ?? ''), '-- Tanpa ruangan --') ?>
                </select>
            </label>
            <label class="field">
                <span>Kuota *</span>
                <input class="<?= has_error('kuota') ?>" type="number" name="kuota" min="1" max="200" value="<?= e(old('kuota', $row['kuota'] ?? 40)) ?>" required>
                <?= error_for('kuota') ?>
            </label>
        </div>
        <?php if (isset($GLOBALS['_errors']['hari'])): ?><div class="alert alert-error"><?= icon('x', 16) ?><span><?= e($GLOBALS['_errors']['hari']) ?></span></div><?php endif; ?>
        <div class="form-actions">
            <a class="btn btn-ghost" href="<?= e(url('admin/kelas')) ?>">Batal</a>
            <button class="btn btn-primary" type="submit"><?= icon('save', 16) ?><span>Simpan</span></button>
        </div>
    </form>
</section>
