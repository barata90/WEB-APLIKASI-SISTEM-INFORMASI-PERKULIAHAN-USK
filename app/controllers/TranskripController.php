<?php
/** Mahasiswa: transkrip nilai sementara dan IPK. */
class TranskripController extends Controller
{
    public function index(): void
    {
        $idMhs = Auth::id('id_mahasiswa');
        $krs = new Krs();
        $this->render('mahasiswa/transkrip', [
            'title'  => 'Transkrip Nilai',
            'profil' => (new Mahasiswa())->profil($idMhs),
            'rows'   => $krs->transkrip($idMhs),
            'ipk'    => $krs->ipk($idMhs),
            'skala'  => (new Nilai())->skala(),
        ]);
    }
}
