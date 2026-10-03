// Mencetak docs/LAPORAN_UTS.html menjadi PDF A4 (Chromium headless).
const { chromium } = require('playwright');
const path = require('path');
(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    await page.goto('file://' + path.join(__dirname, '..', 'docs', 'LAPORAN_UTS.html'), { waitUntil: 'networkidle' });
    await page.pdf({
        path: path.join(__dirname, '..', 'docs', 'LAPORAN_UTS.pdf'),
        format: 'A4', printBackground: true, preferCSSPageSize: true,
        displayHeaderFooter: true,
        headerTemplate: '<div style="font-size:7pt;width:100%;text-align:right;padding-right:16mm;color:#6b7280">Laporan UTS · SIAKAD · Manajemen dan Pemodelan Data</div>',
        footerTemplate: '<div style="font-size:8pt;width:100%;text-align:center;color:#6b7280"><span class="pageNumber"></span> / <span class="totalPages"></span></div>',
    });
    await browser.close();
})();
