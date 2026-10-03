<?php
/** Dosen wali: memeriksa dan menyetujui KRS mahasiswa bimbingan. */
class PerwalianController extends Controller
{
    public array $postActions = ['setujui', 'tolak'];

    public function index(): void
    {
        $ta = (new TahunAkademik())->aktif();
        $this->render('dosen/perwalian', [
            'title'     => 'Perwalian & Persetujuan KRS',
            'ta'        => $ta,
            'bimbingan' => $ta ? (new Mahasiswa())->bimbingan(Auth::id('id_dosen'), (int) $ta['id_ta']) : [],
        ]);
    }

    public function detail(): void
    {
        $ta = (new TahunAkademik())->aktif() ?? abort(404, 'Belum ada tahun akademik aktif.');
        $mhs = $this->mahasiswaBimbingan($this->idParam());
        $krs = new Krs();
        $this->render('dosen/perwalian_detail', [
            'title' => 'KRS ' . $mhs['nama_mahasiswa'],
            'ta'    => $ta,
            'mhs'   => $mhs,
            'rows'  => $krs->byMahasiswa((int) $mhs['id_mahasiswa'], (int) $ta['id_ta']),
            'ipk'   => $krs->ipk((int) $mhs['id_mahasiswa']),
        ]);
    }

    public function setujui(): void
    {
        $this->ubahStatus('Disetujui');
    }

    public function tolak(): void
    {
        $this->ubahStatus('Ditolak');
    }

    private function ubahStatus(string $status): void
    {
        $ta = (new TahunAkademik())->aktif() ?? abort(404);
        $mhs = $this->mahasiswaBimbingan($this->idParam());
        $n = (new Krs())->setStatusPerwalian((int) $mhs['id_mahasiswa'], Auth::id('id_dosen'), (int) $ta['id_ta'], $status);
        flash('success', "$n mata kuliah pada KRS {$mhs['nama_mahasiswa']} telah " . strtolower($status) . '.');
        redirect('dosen/perwalian', ['action' => 'detail', 'id' => $mhs['id_mahasiswa']]);
    }

    private function mahasiswaBimbingan(int $id): array
    {
        $mhs = (new Mahasiswa())->profil($id) ?? abort(404);
        if ((int) $mhs['id_dosen_wali'] !== Auth::id('id_dosen')) {
            abort(403, 'Mahasiswa ini bukan bimbingan Anda.');
        }
        return $mhs;
    }
}
