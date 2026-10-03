/**
 * Mengambil screenshot halaman SIAKAD secara otomatis (Playwright + Chromium)
 * untuk lampiran laporan. Skenario mengikuti alur nyata:
 * admin mengelola data -> mahasiswa mengisi KRS -> dosen wali menyetujui
 * -> dosen menginput nilai -> mahasiswa melihat KHS dan transkrip.
 *
 * Pemakaian (database harus dalam kondisi seed awal):
 *   NODE_PATH=$(npm root -g) node tools/screenshot.js http://localhost/siakad
 */
const { chromium } = require('playwright');
const path = require('path');

const BASE = (process.argv[2] || 'http://localhost/siakad').replace(/\/$/, '');
const OUT = path.join(__dirname, '..', 'docs', 'screenshots');
let n = 0;

async function shot(page, name, opts = {}) {
    n += 1;
    const file = path.join(OUT, `${String(n).padStart(2, '0')}-${name}.png`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: file, fullPage: opts.fullPage ?? false });
    console.log('saved', path.basename(file));
}

const go = (page, query) => page.goto(`${BASE}/index.php?${query}`);

async function login(page, username, password) {
    await go(page, 'page=login');
    await page.fill('input[name=username]', username);
    await page.fill('input[name=password]', password);
    await Promise.all([page.waitForNavigation(), page.click('button[type=submit]')]);
}

async function logout(page) {
    await Promise.all([page.waitForNavigation(), page.click('.topbar-user form button')]);
}

(async () => {
    const browser = await chromium.launch({ args: ['--lang=id-ID'] });
    const context = await browser.newContext({ viewport: { width: 1400, height: 860 }, deviceScaleFactor: 1.5, locale: 'id-ID' });
    const page = await context.newPage();
    page.on('dialog', (d) => d.accept());

    // ---------------- Login
    await go(page, 'page=login');
    await page.click('.demo-accounts summary');
    await shot(page, 'login');
    await page.fill('input[name=username]', 'admin');
    await page.fill('input[name=password]', 'salah');
    await Promise.all([page.waitForNavigation(), page.click('button[type=submit]')]);
    await shot(page, 'login-gagal');

    // ---------------- Administrator
    await login(page, 'admin', 'admin123');
    await shot(page, 'admin-dashboard', { fullPage: true });
    await go(page, 'page=admin/fakultas');
    await shot(page, 'admin-fakultas');
    await go(page, 'page=admin/prodi');
    await shot(page, 'admin-prodi');
    await go(page, 'page=admin/dosen');
    await shot(page, 'admin-dosen');
    await go(page, 'page=admin/mahasiswa');
    await shot(page, 'admin-mahasiswa', { fullPage: true });
    await go(page, 'page=admin/mahasiswa&cari=&prodi=2&angkatan=2025');
    await shot(page, 'admin-mahasiswa-filter');

    // Create mahasiswa: validasi gagal lalu berhasil
    await go(page, 'page=admin/mahasiswa&action=create');
    await page.fill('input[name=npm]', '26081070');
    await page.fill('input[name=nama_mahasiswa]', 'Teuku Ahmad Fauzan');
    await page.fill('input[name=email]', 'bukan-email');
    // matikan validasi HTML5 agar validasi sisi server yang diperlihatkan
    await page.$eval('.form-card form', (f) => { f.noValidate = true; });
    await Promise.all([page.waitForNavigation(), page.click('.form-card button[type=submit]')]);
    await shot(page, 'admin-mahasiswa-validasi');
    await page.fill('input[name=npm]', '2608107010007');
    await page.fill('input[name=nama_mahasiswa]', 'Teuku Ahmad Fauzan');
    await page.selectOption('select[name=jenis_kelamin]', 'L');
    await page.fill('input[name=tanggal_lahir]', '2008-05-14');
    await page.fill('input[name=email]', '2608107010007@mhs.siakad.test');
    await page.fill('input[name=angkatan]', '2026');
    await page.selectOption('select[name=id_prodi]', '1');
    await page.selectOption('select[name=id_dosen_wali]', '6');
    await shot(page, 'admin-mahasiswa-form-tambah');
    await Promise.all([page.waitForNavigation(), page.click('.form-card button[type=submit]')]);
    await shot(page, 'admin-mahasiswa-tambah-berhasil');

    // CRUD mata kuliah
    await go(page, 'page=admin/mata-kuliah&prodi=1');
    await shot(page, 'admin-mata-kuliah', { fullPage: true });
    await go(page, 'page=admin/mata-kuliah&action=create');
    await page.fill('input[name=kode_mk]', 'INF306');
    await page.fill('input[name=nama_mk]', 'Data Warehouse');
    await page.fill('input[name=sks]', '3');
    await page.fill('input[name=semester_paket]', '3');
    await page.selectOption('select[name=jenis]', 'Pilihan');
    await page.selectOption('select[name=id_prodi]', '1');
    await shot(page, 'admin-mk-form-tambah');
    await Promise.all([page.waitForNavigation(), page.click('.form-card button[type=submit]')]);
    await go(page, 'page=admin/mata-kuliah&cari=INF306');
    await shot(page, 'admin-mk-tambah-berhasil');
    await Promise.all([page.waitForNavigation(), page.click('a:has-text("Ubah")')]);
    await page.fill('input[name=nama_mk]', 'Data Warehouse dan OLAP');
    await page.fill('input[name=semester_paket]', '5');
    await shot(page, 'admin-mk-form-ubah');
    await Promise.all([page.waitForNavigation(), page.click('.form-card button[type=submit]')]);
    await go(page, 'page=admin/mata-kuliah&cari=INF306');
    await shot(page, 'admin-mk-ubah-berhasil');
    await Promise.all([page.waitForNavigation(), page.click('button:has-text("Hapus")')]);
    await shot(page, 'admin-mk-hapus-berhasil');
    await go(page, 'page=admin/mata-kuliah&cari=INF301');
    await Promise.all([page.waitForNavigation(), page.click('button:has-text("Hapus")')]);
    await shot(page, 'admin-mk-hapus-ditolak-fk');

    await go(page, 'page=admin/ruangan');
    await shot(page, 'admin-ruangan');
    await go(page, 'page=admin/tahun-akademik');
    await shot(page, 'admin-tahun-akademik');
    await go(page, 'page=admin/kelas&ta=3&prodi=1');
    await shot(page, 'admin-kelas', { fullPage: true });

    // Kelas bentrok
    await go(page, 'page=admin/kelas&action=create');
    await page.selectOption('select[name=id_mk]', '12');
    await page.selectOption('select[name=id_dosen]', '2');
    await page.fill('input[name=nama_kelas]', '02');
    await page.selectOption('select[name=hari]', 'Senin');
    await page.fill('input[name=jam_mulai]', '10:30');
    await page.fill('input[name=jam_selesai]', '12:10');
    await page.selectOption('select[name=id_ruangan]', '2');
    await Promise.all([page.waitForNavigation(), page.click('.form-card button[type=submit]')]);
    await shot(page, 'admin-kelas-bentrok');

    await go(page, 'page=admin/pengguna');
    await shot(page, 'admin-pengguna');
    await go(page, 'page=admin/sistem');
    await shot(page, 'admin-informasi-sistem', { fullPage: true });
    await logout(page);

    // ---------------- Mahasiswa mengisi KRS
    await login(page, '2508107010001', 'mhs123');
    await shot(page, 'mhs-dashboard', { fullPage: true });
    await go(page, 'page=mahasiswa/krs');
    await shot(page, 'mhs-krs-sebelum', { fullPage: true });
    await Promise.all([page.waitForNavigation(), page.click('tr:has-text("INF305") button:has-text("Ambil")')]);
    await shot(page, 'mhs-krs-ambil-berhasil', { fullPage: true });
    await logout(page);

    // ---------------- Dosen wali menyetujui KRS & dosen input nilai
    await login(page, '0011028108', 'dosen123');
    await shot(page, 'dosen-dashboard', { fullPage: true });
    await go(page, 'page=dosen/perwalian');
    await shot(page, 'dosen-perwalian', { fullPage: true });
    await go(page, 'page=dosen/perwalian&action=detail&id=1');
    await shot(page, 'dosen-perwalian-detail');
    await Promise.all([page.waitForNavigation(), page.click('button:has-text("Setujui")')]);
    await shot(page, 'dosen-perwalian-disetujui');
    await go(page, 'page=dosen/kelas');
    await shot(page, 'dosen-kelas');
    await go(page, 'page=dosen/kelas&action=nilai&id=13');
    // isi UTS dan UAS beberapa mahasiswa (nilai tugas sudah ada dari seed)
    const rows = await page.$$('.table-input tbody tr');
    const sampel = [[80, 85], [72.5, 78], [90, 88], [65, 70], [58, 61], [84, 91], [77, 80], [69, 73]];
    for (let i = 0; i < rows.length && i < sampel.length; i++) {
        await rows[i].$eval('input[data-field=nilai_tugas]', (el) => { if (el.value === '') { el.value = '82'; el.dispatchEvent(new Event('input')); } });
        await rows[i].$eval('input[data-field=nilai_uts]', (el, v) => { el.value = v; el.dispatchEvent(new Event('input')); }, String(sampel[i][0]));
        await rows[i].$eval('input[data-field=nilai_uas]', (el, v) => { el.value = v; el.dispatchEvent(new Event('input')); }, String(sampel[i][1]));
    }
    await rows[1].$eval('input[data-field=nilai_uas]', (el) => { el.value = '150'; });
    await page.$eval('form:has(.table-input)', (f) => { f.noValidate = true; });
    await shot(page, 'dosen-input-nilai-form', { fullPage: true });
    await Promise.all([page.waitForNavigation(), page.click('button:has-text("Simpan Nilai")')]);
    await shot(page, 'dosen-input-nilai-validasi');
    const rows2 = await page.$$('.table-input tbody tr');
    await rows2[1].$eval('input[data-field=nilai_uas]', (el) => { el.value = '78'; });
    await Promise.all([page.waitForNavigation(), page.click('button:has-text("Simpan Nilai")')]);
    await shot(page, 'dosen-input-nilai-tersimpan', { fullPage: true });
    await logout(page);

    // ---------------- Mahasiswa melihat hasil
    await login(page, '2508107010001', 'mhs123');
    await go(page, 'page=mahasiswa/krs');
    await shot(page, 'mhs-krs-disetujui');
    await go(page, 'page=mahasiswa/khs&ta=2');
    await shot(page, 'mhs-khs-semester-genap', { fullPage: true });
    await go(page, 'page=mahasiswa/khs&ta=3');
    await shot(page, 'mhs-khs-semester-berjalan', { fullPage: true });
    await go(page, 'page=mahasiswa/transkrip');
    await shot(page, 'mhs-transkrip', { fullPage: true });
    await go(page, 'page=admin/mahasiswa');
    await shot(page, 'mhs-akses-ditolak-403');
    await go(page, 'page=profil');
    await shot(page, 'profil-ganti-password');

    // Tampilan ponsel
    const mobile = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true });
    const mp = await mobile.newPage();
    await mp.goto(`${BASE}/index.php?page=login`);
    await mp.fill('input[name=username]', '2508107010001');
    await mp.fill('input[name=password]', 'mhs123');
    await Promise.all([mp.waitForNavigation(), mp.click('button[type=submit]')]);
    await shot(mp, 'mobile-dashboard-mahasiswa');

    await browser.close();
})();
