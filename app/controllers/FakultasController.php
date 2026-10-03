<?php
class FakultasController extends Controller
{
    private Fakultas $model;
    private array $rules = [
        'kode_fakultas' => 'required|max:10|regex:/^[A-Za-z0-9-]+$/',
        'nama_fakultas' => 'required|max:100',
    ];
    private array $labels = ['kode_fakultas' => 'Kode fakultas', 'nama_fakultas' => 'Nama fakultas'];

    public function __construct()
    {
        $this->model = new Fakultas();
    }

    public function index(): void
    {
        $this->render('admin/fakultas/index', ['title' => 'Data Fakultas', 'rows' => $this->model->all()]);
    }

    public function create(): void
    {
        $this->render('admin/fakultas/form', ['title' => 'Tambah Fakultas', 'row' => null]);
    }

    public function store(): void
    {
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/fakultas', ['action' => 'create']);
        }
        $this->model->create($_POST);
        flash('success', 'Fakultas berhasil ditambahkan.');
        redirect('admin/fakultas');
    }

    public function edit(): void
    {
        $row = $this->model->find($this->idParam()) ?? abort(404);
        $this->render('admin/fakultas/form', ['title' => 'Ubah Fakultas', 'row' => $row]);
    }

    public function update(): void
    {
        $id = $this->idParam();
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/fakultas', ['action' => 'edit', 'id' => $id]);
        }
        $this->model->update($id, $_POST);
        flash('success', 'Fakultas berhasil diperbarui.');
        redirect('admin/fakultas');
    }

    public function delete(): void
    {
        $this->model->delete($this->idParam());
        flash('success', 'Fakultas berhasil dihapus.');
        redirect('admin/fakultas');
    }
}
