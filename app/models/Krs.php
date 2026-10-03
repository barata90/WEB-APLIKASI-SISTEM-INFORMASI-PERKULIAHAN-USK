<?php
class Krs extends Model
{
    public const MAKS_SKS = 24;

    /** KRS mahasiswa pada satu tahun akademik. */
    public function byMahasiswa(int $idMahasiswa, int $idTa): array
    {
        return $this->fetchAll(
            "SELECT k.id_krs, k.status, k.tanggal_pengajuan,
                    kl.id_kelas, kl.nama_kelas, kl.hari, kl.jam_mulai, kl.jam_selesai,
                    mk.kode_mk, mk.nama_mk, mk.sks, d.nama_dosen, r.kode_ruangan
             FROM krs k
             JOIN kelas kl         ON kl.id_kelas = k.id_kelas
             JOIN mata_kuliah mk   ON mk.id_mk = kl.id_mk
             JOIN dosen d          ON d.id_dosen = kl.id_dosen
             LEFT JOIN ruangan r   ON r.id_ruangan = kl.id_ruangan
             WHERE k.id_mahasiswa = ? AND kl.id_ta = ?
             ORDER BY FIELD(kl.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), kl.jam_mulai",
            [$idMahasiswa, $idTa]
        );
    }

    /**
     * Mahasiswa mengambil kelas. Aturan bisnis diperiksa dalam satu transaksi:
     * kelas harus di TA aktif dan prodi yang sama, mata kuliah belum diambil,
     * kuota masih tersedia, jadwal tidak bentrok, dan total SKS <= MAKS_SKS.
     * Mengembalikan null jika berhasil, atau pesan galat.
     */
    public function ambil(int $idMahasiswa, int $idKelas): ?string
    {
        $this->db->beginTransaction();
        try {
            // Kunci baris kelas agar perhitungan kuota aman dari request bersamaan.
            $kelas = $this->fetchOne(
                "SELECT kl.*, mk.sks, mk.id_prodi, mk.nama_mk, t.is_aktif
                 FROM kelas kl
                 JOIN mata_kuliah mk   ON mk.id_mk = kl.id_mk
                 JOIN tahun_akademik t ON t.id_ta = kl.id_ta
                 WHERE kl.id_kelas = ?
                 FOR UPDATE",
                [$idKelas]
            );
            $mhs = $this->fetchOne('SELECT id_prodi FROM mahasiswa WHERE id_mahasiswa = ?', [$idMahasiswa]);

            $error = match (true) {
                !$kelas || !$mhs                         => 'Kelas tidak ditemukan.',
                (int) $kelas['is_aktif'] !== 1           => 'Kelas bukan pada tahun akademik aktif.',
                $kelas['id_prodi'] != $mhs['id_prodi']   => 'Kelas bukan milik program studi Anda.',
                default                                  => null,
            };

            $error ??= $this->scalar(
                "SELECT 1 FROM krs k JOIN kelas kl ON kl.id_kelas = k.id_kelas
                 WHERE k.id_mahasiswa = ? AND kl.id_ta = ? AND kl.id_mk = ? AND k.status <> 'Ditolak'",
                [$idMahasiswa, $kelas['id_ta'], $kelas['id_mk']]
            ) ? 'Mata kuliah ini sudah ada di KRS Anda.' : null;

            if ($error === null) {
                $terisi = (int) $this->scalar(
                    "SELECT COUNT(*) FROM krs WHERE id_kelas = ? AND status <> 'Ditolak'",
                    [$idKelas]
                );
                if ($terisi >= (int) $kelas['kuota']) {
                    $error = 'Kuota kelas sudah penuh.';
                }
            }

            if ($error === null) {
                $bentrok = $this->fetchOne(
                    "SELECT mk.nama_mk, kl.hari, kl.jam_mulai, kl.jam_selesai
                     FROM krs k
                     JOIN kelas kl       ON kl.id_kelas = k.id_kelas
                     JOIN mata_kuliah mk ON mk.id_mk = kl.id_mk
                     WHERE k.id_mahasiswa = ? AND kl.id_ta = ? AND k.status <> 'Ditolak'
                       AND kl.hari = ? AND kl.jam_mulai < ? AND kl.jam_selesai > ?
                     LIMIT 1",
                    [$idMahasiswa, $kelas['id_ta'], $kelas['hari'], $kelas['jam_selesai'], $kelas['jam_mulai']]
                );
                if ($bentrok) {
                    $error = sprintf('Jadwal bentrok dengan %s (%s %s-%s).', $bentrok['nama_mk'], $bentrok['hari'],
                        jam($bentrok['jam_mulai']), jam($bentrok['jam_selesai']));
                }
            }

            if ($error === null) {
                $sks = (int) $this->scalar(
                    "SELECT COALESCE(SUM(mk.sks), 0)
                     FROM krs k
                     JOIN kelas kl       ON kl.id_kelas = k.id_kelas
                     JOIN mata_kuliah mk ON mk.id_mk = kl.id_mk
                     WHERE k.id_mahasiswa = ? AND kl.id_ta = ? AND k.status <> 'Ditolak'",
                    [$idMahasiswa, $kelas['id_ta']]
                );
                if ($sks + (int) $kelas['sks'] > self::MAKS_SKS) {
                    $error = 'Total SKS melebihi batas ' . self::MAKS_SKS . ' SKS.';
                }
            }

            if ($error !== null) {
                $this->db->rollBack();
                return $error;
            }

            $this->execute(
                "INSERT INTO krs (id_mahasiswa, id_kelas, status) VALUES (?, ?, 'Diajukan')",
                [$idMahasiswa, $idKelas]
            );
            $this->db->commit();
            return null;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Membatalkan KRS milik mahasiswa sendiri yang belum disetujui. */
    public function batal(int $idKrs, int $idMahasiswa): int
    {
        return $this->execute(
            "DELETE FROM krs WHERE id_krs = ? AND id_mahasiswa = ? AND status IN ('Diajukan', 'Ditolak')",
            [$idKrs, $idMahasiswa]
        );
    }

    /** Dosen wali menyetujui/menolak seluruh KRS yang diajukan mahasiswa bimbingannya. */
    public function setStatusPerwalian(int $idMahasiswa, int $idDosenWali, int $idTa, string $status): int
    {
        return $this->execute(
            "UPDATE krs k
             JOIN mahasiswa m ON m.id_mahasiswa = k.id_mahasiswa
             JOIN kelas kl    ON kl.id_kelas = k.id_kelas
             SET k.status = ?
             WHERE k.id_mahasiswa = ? AND m.id_dosen_wali = ? AND kl.id_ta = ? AND k.status = 'Diajukan'",
            [$status, $idMahasiswa, $idDosenWali, $idTa]
        );
    }

    /** Kartu Hasil Studi: nilai satu mahasiswa di satu tahun akademik. */
    public function khs(int $idMahasiswa, int $idTa): array
    {
        return $this->fetchAll(
            "SELECT mk.kode_mk, mk.nama_mk, mk.sks, kl.nama_kelas, d.nama_dosen,
                    v.nilai_tugas, v.nilai_uts, v.nilai_uas, v.nilai_akhir, v.huruf, v.bobot,
                    v.sks * v.bobot AS mutu
             FROM krs k
             JOIN kelas kl         ON kl.id_kelas = k.id_kelas
             JOIN mata_kuliah mk   ON mk.id_mk = kl.id_mk
             JOIN dosen d          ON d.id_dosen = kl.id_dosen
             LEFT JOIN v_nilai_akhir v ON v.id_krs = k.id_krs
             WHERE k.id_mahasiswa = ? AND kl.id_ta = ? AND k.status = 'Disetujui'
             ORDER BY mk.kode_mk",
            [$idMahasiswa, $idTa]
        );
    }

    /** Daftar tahun akademik yang pernah diikuti mahasiswa. */
    public function semesterDiikuti(int $idMahasiswa): array
    {
        return $this->fetchAll(
            "SELECT DISTINCT t.id_ta, t.tahun, t.semester, t.is_aktif, ips.ips, ips.total_sks
             FROM krs k
             JOIN kelas kl         ON kl.id_kelas = k.id_kelas
             JOIN tahun_akademik t ON t.id_ta = kl.id_ta
             LEFT JOIN v_ips ips   ON ips.id_mahasiswa = k.id_mahasiswa AND ips.id_ta = t.id_ta
             WHERE k.id_mahasiswa = ?
             ORDER BY t.tahun, t.semester",
            [$idMahasiswa]
        );
    }

    /** Transkrip: mata kuliah bernilai lengkap; bila diulang, diambil nilai terbaik. */
    public function transkrip(int $idMahasiswa): array
    {
        return $this->fetchAll(
            "SELECT v.kode_mk, v.nama_mk, v.sks, v.nilai_akhir, v.huruf, v.bobot,
                    v.sks * v.bobot AS mutu, mk.semester_paket, t.tahun, t.semester
             FROM v_nilai_akhir v
             JOIN mata_kuliah mk   ON mk.id_mk = v.id_mk
             JOIN tahun_akademik t ON t.id_ta = v.id_ta
             WHERE v.id_mahasiswa = ? AND v.huruf IS NOT NULL
               AND v.id_nilai = (
                   SELECT v2.id_nilai FROM v_nilai_akhir v2
                   WHERE v2.id_mahasiswa = v.id_mahasiswa AND v2.id_mk = v.id_mk AND v2.huruf IS NOT NULL
                   ORDER BY v2.bobot DESC, v2.nilai_akhir DESC
                   LIMIT 1)
             ORDER BY mk.semester_paket, v.kode_mk",
            [$idMahasiswa]
        );
    }

    public function ipk(int $idMahasiswa): ?array
    {
        return $this->fetchOne('SELECT ipk, total_sks FROM v_ipk WHERE id_mahasiswa = ?', [$idMahasiswa]);
    }
}
