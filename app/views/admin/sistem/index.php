<div class="grid-2">
    <section class="card">
        <div class="card-head"><h2>Server &amp; Lingkungan</h2></div>
        <dl class="kv kv-wide">
            <?php foreach ($info as $k => $v): ?>
                <dt><?= e($k) ?></dt><dd><code><?= e($v) ?></code></dd>
            <?php endforeach; ?>
        </dl>
    </section>
    <section class="card">
        <div class="card-head"><h2>Objek Basis Data <code><?= e(config('db_name')) ?></code></h2></div>
        <div class="table-wrap">
            <table class="table table-compact">
                <thead><tr><th>Nama</th><th>Jenis</th><th>Engine</th><th class="num">Jumlah Baris</th></tr></thead>
                <tbody>
                <?php foreach ($tabel as $t): ?>
                    <tr>
                        <td><code><?= e($t['nama']) ?></code></td>
                        <td><?= $t['jenis'] === 'VIEW' ? 'VIEW' : 'TABLE' ?></td>
                        <td><?= e($t['engine'] ?? '-') ?></td>
                        <td class="num"><?= $t['jumlah_baris'] !== null ? e($t['jumlah_baris']) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<section class="card">
    <div class="card-head"><h2>Relasi Foreign Key</h2></div>
    <div class="table-wrap">
        <table class="table table-compact">
            <thead><tr><th>Constraint</th><th>Tabel.Kolom (FK)</th><th>Merujuk ke (PK)</th><th>ON UPDATE</th><th>ON DELETE</th></tr></thead>
            <tbody>
            <?php foreach ($fk as $f): ?>
                <tr>
                    <td><code><?= e($f['nama']) ?></code></td>
                    <td><code><?= e($f['tabel'] . '.' . $f['kolom']) ?></code></td>
                    <td><code><?= e($f['ref_tabel'] . '.' . $f['ref_kolom']) ?></code></td>
                    <td><?= e($f['on_update']) ?></td>
                    <td><?= e($f['on_delete']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
