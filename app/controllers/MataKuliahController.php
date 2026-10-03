<?php
class MataKuliahController extends Controller
{
    private MataKuliah $model;
    private array $rules = [
        'kode_mk'        => 'required|max:10|regex:/^[A-Za-z0-9]+$/',
        'nama_mk'        => 'required|max:100',
        'sks'            => 'required|int|min:1|max:6',
        'semester_paket' => 'required|int|min:1|max:8',
        'jenis'          => 'required|in:Wajib,Pilihan',
        'id_prodi'       => 'required|int',
    ];
    private array $labels = [
        'kode_mk' => 'Kode mata kuliah', 'nama_mk' => 'Nama mata kuliah', 'sks' => 'SKS',
        'semester_paket' => 'Semester', 'id_prodi' => 'Program studi',
    ];

    public function __construct()
    {
        $this->model = new MataKuliah();
    }

    public function index(): void
    {
        $cari = (string) input('cari', '');
        $idProdi = (int) input('prodi', 0) ?: null;
        $this->render('admin/mata_kuliah/index', [
            'title'   => 'Data Mata Kuliah',
            'rows'    => $this->model->all($cari, $idProdi),
            'prodi'   => (new Prodi())->options(),
            'cari'    => $cari,
            'idProdi' => $idProdi,
        ]);
    }

    public function create(): void
    {
        $this->form(null, 'Tambah Mata Kuliah');
    }

    public function store(): void
    {
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/mata-kuliah', ['action' => 'create']);
        }
        $this->model->create($_POST);
        flash('success', 'Mata kuliah ' . $_POST['nama_mk'] . ' berhasil ditambahkan.');
        redirect('admin/mata-kuliah');
    }

    public function edit(): void
    {
        $this->form($this->model->find($this->idParam()) ?? abort(404), 'Ubah Mata Kuliah');
    }

    public function update(): void
    {
        $id = $this->idParam();
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/mata-kuliah', ['action' => 'edit', 'id' => $id]);
        }
        $this->model->update($id, $_POST);
        flash('success', 'Mata kuliah berhasil diperbarui.');
        redirect('admin/mata-kuliah');
    }

    public function delete(): void
    {
        $this->model->delete($this->idParam());
        flash('success', 'Mata kuliah berhasil dihapus.');
        redirect('admin/mata-kuliah');
    }

    private function form(?array $row, string $title): void
    {
        $this->render('admin/mata_kuliah/form', ['title' => $title, 'row' => $row, 'prodi' => (new Prodi())->options()]);
    }
}
