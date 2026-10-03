// Interaksi kecil di sisi klien (tanpa library eksternal).
(function () {
    // Hitung nilai akhir & huruf secara langsung saat dosen mengetik nilai.
    var skalaEl = document.getElementById('skala');
    if (skalaEl) {
        var skala = JSON.parse(skalaEl.dataset.skala || '[]');
        var cls = { A: 'badge-success', AB: 'badge-success', B: 'badge-info', BC: 'badge-info', C: 'badge-warning', D: 'badge-danger', E: 'badge-danger' };
        document.querySelectorAll('.table-input tbody tr').forEach(function (tr) {
            var inputs = tr.querySelectorAll('.nilai-input');
            if (!inputs.length) return;
            var update = function () {
                var v = {};
                inputs.forEach(function (i) { v[i.dataset.field] = i.value === '' ? null : parseFloat(i.value); });
                var out = tr.querySelector('.nilai-akhir');
                var badge = tr.querySelector('.huruf');
                if (v.nilai_tugas === null || v.nilai_uts === null || v.nilai_uas === null) {
                    out.textContent = '-'; badge.textContent = '-'; badge.className = 'badge huruf badge-muted';
                    return;
                }
                var na = Math.round((0.3 * v.nilai_tugas + 0.3 * v.nilai_uts + 0.4 * v.nilai_uas) * 100) / 100;
                out.textContent = na.toFixed(2).replace('.', ',');
                var h = skala.find(function (s) { return na >= s[1] && na < s[2]; });
                badge.textContent = h ? h[0] : '-';
                badge.className = 'badge huruf ' + (h ? cls[h[0]] : 'badge-muted');
            };
            inputs.forEach(function (i) { i.addEventListener('input', update); });
        });
    }

    // Tutup notifikasi otomatis setelah 6 detik.
    setTimeout(function () {
        document.querySelectorAll('.content > .alert-success').forEach(function (a) { a.style.opacity = '0'; });
    }, 6000);
})();
