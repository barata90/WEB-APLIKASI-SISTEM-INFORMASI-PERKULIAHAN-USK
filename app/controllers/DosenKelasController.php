<?php
/** Dosen: daftar kelas yang diampu dan input nilai mahasiswa. */
class DosenKelasController extends Controller
{
    public array $postActions = ['simpanNilai'];

    public function index(): void
    {
        $taModel = new TahunAkademik();
        $aktif = $taModel->aktif();
        $idTa = isset($_GET['ta']) ? ((int) $_GET['ta'] ?: null) : ($aktif['id_ta'] ?? null);
        $this->render('dosen/kelas', [
            'title'  => 'Kelas yang Diampu',
            'rows'   => (new Kelas())->byDosen(Auth::id('id_dosen'), $idTa),
            'taList' => $taModel->options(),
            'idTa'   => $idTa,
        ]);
    }

    public function nilai(): void
    {
        $kelas = $this->kelasMilikSaya($this->idParam());
        $this->render('dosen/nilai', [
            'title'   => 'Input Nilai ' . $kelas['nama_mk'],
            'kelas'   => $kelas,
            'peserta' => (new Kelas())->peserta((int) $kelas['id_kelas']),
            'skala'   => (new Nilai())->skala(),
            'oldNilai' => $this->pullOldNilai(),
        ]);
    }

    public function simpanNilai(): void
    {
        $kelas = $this->kelasMilikSaya($this->idParam());
        $input = $_POST['nilai'] ?? [];
        $rows = [];
        $errors = [];

        foreach ((array) $input as $idKrs => $n) {
            $row = [];
            foreach (['nilai_tugas', 'nilai_uts', 'nilai_uas'] as $field) {
                $v = trim(str_replace(',', '.', (string) ($n[$field] ?? '')));
                if ($v === '') {
                    $row[$field] = null;
                } elseif (!is_numeric($v) || $v < 0 || $v > 100) {
                    $errors["nilai.$idKrs.$field"] = 'Nilai harus angka 0-100.';
                    $row[$field] = null;
                } else {
                    $row[$field] = round((float) $v, 2);
                }
            }
            $rows[(int) $idKrs] = $row;
        }

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old_nilai'] = $input;
            flash('error', 'Ada ' . count($errors) . ' nilai yang tidak valid (harus angka 0 sampai 100).');
            redirect('dosen/kelas', ['action' => 'nilai', 'id' => $kelas['id_kelas']]);
        }

        $n = (new Nilai())->simpanKelas((int) $kelas['id_kelas'], $rows);
        flash('success', "Nilai $n mahasiswa pada kelas {$kelas['nama_mk']} berhasil disimpan.");
        redirect('dosen/kelas', ['action' => 'nilai', 'id' => $kelas['id_kelas']]);
    }

    private function pullOldNilai(): array
    {
        $old = $_SESSION['_old_nilai'] ?? [];
        unset($_SESSION['_old_nilai']);
        return is_array($old) ? $old : [];
    }

    /** Pastikan kelas memang diampu dosen yang sedang login. */
    private function kelasMilikSaya(int $idKelas): array
    {
        $kelas = (new Kelas())->find($idKelas) ?? abort(404, 'Kelas tidak ditemukan.');
        if ((int) $kelas['id_dosen'] !== Auth::id('id_dosen')) {
            abort(403, 'Anda bukan dosen pengampu kelas ini.');
        }
        return $kelas;
    }
}
