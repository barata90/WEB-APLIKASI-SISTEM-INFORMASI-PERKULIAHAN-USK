<?php
/** Mahasiswa: Kartu Hasil Studi per semester. */
class KhsController extends Controller
{
    public function index(): void
    {
        $idMhs = Auth::id('id_mahasiswa');
        $krs = new Krs();
        $semester = $krs->semesterDiikuti($idMhs);
        $idTa = (int) input('ta', 0);
        if (!$idTa && $semester) {
            // bawaan: semester terakhir yang sudah memiliki IPS
            $denganIps = array_filter($semester, fn($s) => $s['ips'] !== null);
            $idTa = (int) (end($denganIps)['id_ta'] ?? end($semester)['id_ta']);
        }
        $current = current(array_filter($semester, fn($s) => (int) $s['id_ta'] === $idTa)) ?: null;

        $this->render('mahasiswa/khs', [
            'title'    => 'Kartu Hasil Studi (KHS)',
            'profil'   => (new Mahasiswa())->profil($idMhs),
            'semester' => $semester,
            'idTa'     => $idTa,
            'current'  => $current,
            'rows'     => $idTa ? $krs->khs($idMhs, $idTa) : [],
            'ipk'      => $krs->ipk($idMhs),
        ]);
    }
}
