<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        $ta = (new TahunAkademik())->aktif();
        $idTa = $ta ? (int) $ta['id_ta'] : null;

        match (Auth::role()) {
            'admin'     => $this->admin($ta, $idTa),
            'dosen'     => $this->dosen($ta, $idTa),
            'mahasiswa' => $this->mahasiswa($ta, $idTa),
        };
    }

    private function admin(?array $ta, ?int $idTa): void
    {
        $stat = new Statistik();
        $this->render('dashboard/admin', [
            'title'      => 'Dashboard Administrator',
            'ta'         => $ta,
            'ringkasan'  => $stat->ringkasan($idTa),
            'perProdi'   => $stat->mahasiswaPerProdi(),
            'sebaran'    => $stat->sebaranHuruf(),
            'akun'       => (new User())->countByRole(),
        ]);
    }

    private function dosen(?array $ta, ?int $idTa): void
    {
        $idDosen = Auth::id('id_dosen');
        $kelas = $idTa ? (new Kelas())->byDosen($idDosen, $idTa) : [];
        $bimbingan = $idTa ? (new Mahasiswa())->bimbingan($idDosen, $idTa) : [];
        $this->render('dashboard/dosen', [
            'title'     => 'Dashboard Dosen',
            'ta'        => $ta,
            'kelas'     => $kelas,
            'bimbingan' => $bimbingan,
            'menunggu'  => array_sum(array_column($bimbingan, 'jumlah_diajukan')),
        ]);
    }

    private function mahasiswa(?array $ta, ?int $idTa): void
    {
        $idMhs = Auth::id('id_mahasiswa');
        $krsModel = new Krs();
        $this->render('dashboard/mahasiswa', [
            'title'    => 'Dashboard Mahasiswa',
            'ta'       => $ta,
            'profil'   => (new Mahasiswa())->profil($idMhs),
            'krs'      => $idTa ? $krsModel->byMahasiswa($idMhs, $idTa) : [],
            'ipk'      => $krsModel->ipk($idMhs),
            'semester' => $krsModel->semesterDiikuti($idMhs),
        ]);
    }
}
