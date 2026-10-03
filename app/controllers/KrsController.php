<?php
/** Mahasiswa: pengisian Kartu Rencana Studi pada tahun akademik aktif. */
class KrsController extends Controller
{
    public array $postActions = ['ambil', 'batal'];

    public function index(): void
    {
        $ta = (new TahunAkademik())->aktif();
        $idMhs = Auth::id('id_mahasiswa');
        $krs = $ta ? (new Krs())->byMahasiswa($idMhs, (int) $ta['id_ta']) : [];
        $this->render('mahasiswa/krs', [
            'title'    => 'Kartu Rencana Studi (KRS)',
            'ta'       => $ta,
            'krs'      => $krs,
            'tersedia' => $ta ? (new Kelas())->tersedia($idMhs, (int) $ta['id_ta']) : [],
            'totalSks' => array_sum(array_map(fn($r) => $r['status'] !== 'Ditolak' ? $r['sks'] : 0, $krs)),
            'maksSks'  => Krs::MAKS_SKS,
            'profil'   => (new Mahasiswa())->profil($idMhs),
        ]);
    }

    public function ambil(): void
    {
        $idKelas = (int) input('id_kelas', 0);
        $error = (new Krs())->ambil(Auth::id('id_mahasiswa'), $idKelas);
        $error ? flash('error', $error) : flash('success', 'Kelas berhasil ditambahkan ke KRS dan menunggu persetujuan dosen wali.');
        redirect('mahasiswa/krs');
    }

    public function batal(): void
    {
        $n = (new Krs())->batal($this->idParam(), Auth::id('id_mahasiswa'));
        $n ? flash('success', 'Mata kuliah dihapus dari KRS.') : flash('error', 'KRS yang sudah disetujui tidak dapat dibatalkan.');
        redirect('mahasiswa/krs');
    }
}
