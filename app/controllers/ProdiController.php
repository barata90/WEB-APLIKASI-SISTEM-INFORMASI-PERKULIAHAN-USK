<?php
class ProdiController extends Controller
{
    private Prodi $model;
    private array $rules = [
        'kode_prodi'  => 'required|max:10|regex:/^[A-Za-z0-9-]+$/',
        'nama_prodi'  => 'required|max:100',
        'jenjang'     => 'required|in:D3,S1,S2,S3',
        'id_fakultas' => 'required|int',
    ];
    private array $labels = ['kode_prodi' => 'Kode prodi', 'nama_prodi' => 'Nama prodi', 'id_fakultas' => 'Fakultas'];

    public function __construct()
    {
        $this->model = new Prodi();
    }

    public function index(): void
    {
        $this->render('admin/prodi/index', ['title' => 'Data Program Studi', 'rows' => $this->model->all()]);
    }

    public function create(): void
    {
        $this->form(null, 'Tambah Program Studi');
    }

    public function store(): void
    {
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/prodi', ['action' => 'create']);
        }
        $this->model->create($_POST);
        flash('success', 'Program studi berhasil ditambahkan.');
        redirect('admin/prodi');
    }

    public function edit(): void
    {
        $this->form($this->model->find($this->idParam()) ?? abort(404), 'Ubah Program Studi');
    }

    public function update(): void
    {
        $id = $this->idParam();
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/prodi', ['action' => 'edit', 'id' => $id]);
        }
        $this->model->update($id, $_POST);
        flash('success', 'Program studi berhasil diperbarui.');
        redirect('admin/prodi');
    }

    public function delete(): void
    {
        $this->model->delete($this->idParam());
        flash('success', 'Program studi berhasil dihapus.');
        redirect('admin/prodi');
    }

    private function form(?array $row, string $title): void
    {
        $this->render('admin/prodi/form', [
            'title'    => $title,
            'row'      => $row,
            'fakultas' => (new Fakultas())->all(),
        ]);
    }
}
