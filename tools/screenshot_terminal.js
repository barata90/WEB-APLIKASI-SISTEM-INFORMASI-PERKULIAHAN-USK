/**
 * Screenshot halaman HTML bergaya terminal hasil tools/capture_terminal.py.
 * Pemakaian: NODE_PATH=$(npm root -g) node tools/screenshot_terminal.js
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const SRC = path.join(__dirname, '..', 'docs', 'terminal', 'html');
const OUT = path.join(__dirname, '..', 'docs', 'screenshots', 'terminal');
fs.mkdirSync(OUT, { recursive: true });

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage({ viewport: { width: 1600, height: 900 }, deviceScaleFactor: 1.5 });
    for (const f of fs.readdirSync(SRC).filter((x) => x.endsWith('.html')).sort()) {
        await page.goto('file://' + path.join(SRC, f));
        await page.locator('.term').screenshot({ path: path.join(OUT, f.replace('.html', '.png')) });
        console.log('saved', f.replace('.html', '.png'));
    }
    await browser.close();
})();
