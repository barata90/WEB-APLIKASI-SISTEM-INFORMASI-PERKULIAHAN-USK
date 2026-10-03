<?php
class KelasController extends Controller
{
    private Kelas $model;
    private array $rules = [
        'id_mk'       => 'required|int',
        'id_ta'       => 'required|int',
        'id_dosen'    => 'required|int',
        'id_ruangan'  => 'int',
        'nama_kelas'  => 'required|max:5|regex:/^[A-Za-z0-9]+$/',
        'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
        'jam_mulai'   => 'required|time',
        'jam_selesai' => 'required|time',
        'kuota'       => 'required|int|min:1|max:200',
    ];
    private array $labels = [
        'id_mk' => 'Mata kuliah', 'id_ta' => 'Tahun akademik', 'id_dosen' => 'Dosen pengampu',
        'id_ruangan' => 'Ruangan', 'nama_kelas' => 'Nama kelas', 'jam_mulai' => 'Jam mulai', 'jam_selesai' => 'Jam selesai',
    ];

    public function __construct()
    {
        $this->model = new Kelas();
    }

    public function index(): void
    {
        $aktif = (new TahunAkademik())->aktif();
        $idTa = isset($_GET['ta']) ? ((int) $_GET['ta'] ?: null) : ($aktif['id_ta'] ?? null);
        $idProdi = (int) input('prodi', 0) ?: null;
        $this->render('admin/kelas/index', [
            'title'   => 'Kelas & Jadwal Kuliah',
            'rows'    => $this->model->all($idTa, $idProdi),
            'taList'  => (new TahunAkademik())->options(),
            'prodi'   => (new Prodi())->options(),
            'idTa'    => $idTa,
            'idProdi' => $idProdi,
        ]);
    }

    public function create(): void
    {
        $this->form(null, 'Tambah Kelas');
    }

    public function store(): void
    {
        if ($errors = $this->check($_POST)) {
            $this->backWithErrors($errors, 'admin/kelas', ['action' => 'create']);
        }
        $this->model->create($_POST);
        flash('success', 'Kelas berhasil ditambahkan.');
        redirect('admin/kelas', ['ta' => $_POST['id_ta']]);
    }

    public function edit(): void
    {
        $this->form($this->model->find($this->idParam()) ?? abort(404), 'Ubah Kelas');
    }

    public function update(): void
    {
        $id = $this->idParam();
        if ($errors = $this->check($_POST, $id)) {
            $this->backWithErrors($errors, 'admin/kelas', ['action' => 'edit', 'id' => $id]);
        }
        $this->model->update($id, $_POST);
        flash('success', 'Kelas berhasil diperbarui.');
        redirect('admin/kelas', ['ta' => $_POST['id_ta']]);
    }

    public function delete(): void
    {
        $this->model->delete($this->idParam());
        flash('success', 'Kelas berhasil dihapus.');
        redirect('admin/kelas');
    }

    private function check(array $d, ?int $id = null): array
    {
        $errors = $this->validate($d, $this->rules, $this->labels);
        if (!$errors && $d['jam_selesai'] <= $d['jam_mulai']) {
            $errors['jam_selesai'] = 'Jam selesai harus lebih besar dari jam mulai.';
        }
        if (!$errors && ($b = $this->model->bentrok($d, $id))) {
            $errors['hari'] = sprintf(
                'Jadwal bentrok (%s sama) dengan %s kelas %s, %s %s-%s.',
                $b['jenis'], $b['nama_mk'], $b['nama_kelas'], $b['hari'], jam($b['jam_mulai']), jam($b['jam_selesai'])
            );
        }
        return $errors;
    }

    private function form(?array $row, string $title): void
    {
        $this->render('admin/kelas/form', [
            'title'   => $title,
            'row'     => $row,
            'mk'      => (new MataKuliah())->options(),
            'taList'  => (new TahunAkademik())->options(),
            'dosen'   => (new Dosen())->options(),
            'ruangan' => (new Ruangan())->all(),
            'aktif'   => (new TahunAkademik())->aktif(),
        ]);
    }
}
