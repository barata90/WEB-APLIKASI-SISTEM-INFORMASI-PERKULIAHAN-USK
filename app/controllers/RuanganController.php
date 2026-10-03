<?php
class RuanganController extends Controller
{
    private Ruangan $model;
    private array $rules = [
        'kode_ruangan' => 'required|max:15|regex:/^[A-Za-z0-9-]+$/',
        'nama_ruangan' => 'required|max:100',
        'gedung'       => 'max:100',
        'kapasitas'    => 'required|int|min:1|max:500',
    ];
    private array $labels = ['kode_ruangan' => 'Kode ruangan', 'nama_ruangan' => 'Nama ruangan'];

    public function __construct()
    {
        $this->model = new Ruangan();
    }

    public function index(): void
    {
        $this->render('admin/ruangan/index', ['title' => 'Data Ruangan', 'rows' => $this->model->all()]);
    }

    public function create(): void
    {
        $this->render('admin/ruangan/form', ['title' => 'Tambah Ruangan', 'row' => null]);
    }

    public function store(): void
    {
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/ruangan', ['action' => 'create']);
        }
        $this->model->create($_POST);
        flash('success', 'Ruangan berhasil ditambahkan.');
        redirect('admin/ruangan');
    }

    public function edit(): void
    {
        $row = $this->model->find($this->idParam()) ?? abort(404);
        $this->render('admin/ruangan/form', ['title' => 'Ubah Ruangan', 'row' => $row]);
    }

    public function update(): void
    {
        $id = $this->idParam();
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/ruangan', ['action' => 'edit', 'id' => $id]);
        }
        $this->model->update($id, $_POST);
        flash('success', 'Ruangan berhasil diperbarui.');
        redirect('admin/ruangan');
    }

    public function delete(): void
    {
        $this->model->delete($this->idParam());
        flash('success', 'Ruangan berhasil dihapus.');
        redirect('admin/ruangan');
    }
}
