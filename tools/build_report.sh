#!/usr/bin/env bash
# Membuat docs/LAPORAN_UTS.pdf dan docs/LAPORAN_UTS.docx dari docs/LAPORAN_UTS.md
set -euo pipefail
cd "$(dirname "$0")/../docs"
pandoc LAPORAN_UTS.md -s --metadata pagetitle="Laporan UTS SIAKAD" --css ../tools/report.css --embed-resources -o LAPORAN_UTS.html
NODE_PATH="${NODE_PATH:-$(npm root -g)}" node ../tools/report_pdf.js
pandoc LAPORAN_UTS.md --resource-path=. -o LAPORAN_UTS.docx
rm -f LAPORAN_UTS.html
ls -lh LAPORAN_UTS.pdf LAPORAN_UTS.docx
