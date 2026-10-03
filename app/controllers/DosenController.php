<?php
class DosenController extends Controller
{
    private Dosen $model;
    private array $rules = [
        'nidn'       => 'required|digits:10',
        'nama_dosen' => 'required|max:100',
        'email'      => 'email|max:100',
        'no_hp'      => 'max:20|regex:/^[0-9+\- ]+$/',
        'id_prodi'   => 'required|int',
    ];
    private array $labels = ['nidn' => 'NIDN', 'nama_dosen' => 'Nama dosen', 'no_hp' => 'No. HP', 'id_prodi' => 'Program studi'];

    public function __construct()
    {
        $this->model = new Dosen();
    }

    public function index(): void
    {
        $cari = (string) input('cari', '');
        $this->render('admin/dosen/index', [
            'title' => 'Data Dosen',
            'rows'  => $this->model->all($cari),
            'cari'  => $cari,
        ]);
    }

    public function create(): void
    {
        $this->form(null, 'Tambah Dosen');
    }

    public function store(): void
    {
        $rules = $this->rules + ['password' => 'required|min:6|max:72'];
        if ($errors = $this->validate($_POST, $rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/dosen', ['action' => 'create']);
        }
        $this->model->create($_POST, $_POST['password']);
        flash('success', 'Dosen berhasil ditambahkan. Akun login: ' . $_POST['nidn']);
        redirect('admin/dosen');
    }

    public function edit(): void
    {
        $this->form($this->model->find($this->idParam()) ?? abort(404), 'Ubah Dosen');
    }

    public function update(): void
    {
        $id = $this->idParam();
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/dosen', ['action' => 'edit', 'id' => $id]);
        }
        $this->model->update($id, $_POST);
        flash('success', 'Data dosen berhasil diperbarui.');
        redirect('admin/dosen');
    }

    public function delete(): void
    {
        $this->model->delete($this->idParam());
        flash('success', 'Dosen berhasil dihapus.');
        redirect('admin/dosen');
    }

    private function form(?array $row, string $title): void
    {
        $this->render('admin/dosen/form', ['title' => $title, 'row' => $row, 'prodi' => (new Prodi())->options()]);
    }
}
