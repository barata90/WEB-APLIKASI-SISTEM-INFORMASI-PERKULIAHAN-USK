<?php
class TahunAkademikController extends Controller
{
    private TahunAkademik $model;
    private array $rules = [
        'tahun'    => 'required|regex:/^\d{4}\/\d{4}$/',
        'semester' => 'required|in:Ganjil,Genap',
    ];

    public function __construct()
    {
        $this->model = new TahunAkademik();
    }

    public function index(): void
    {
        $this->render('admin/tahun_akademik/index', ['title' => 'Tahun Akademik', 'rows' => $this->model->all()]);
    }

    public function create(): void
    {
        $this->render('admin/tahun_akademik/form', ['title' => 'Tambah Tahun Akademik', 'row' => null]);
    }

    public function store(): void
    {
        if ($errors = $this->check($_POST)) {
            $this->backWithErrors($errors, 'admin/tahun-akademik', ['action' => 'create']);
        }
        $this->model->create($_POST);
        flash('success', 'Tahun akademik berhasil ditambahkan.');
        redirect('admin/tahun-akademik');
    }

    public function edit(): void
    {
        $row = $this->model->find($this->idParam()) ?? abort(404);
        $this->render('admin/tahun_akademik/form', ['title' => 'Ubah Tahun Akademik', 'row' => $row]);
    }

    public function update(): void
    {
        $id = $this->idParam();
        if ($errors = $this->check($_POST)) {
            $this->backWithErrors($errors, 'admin/tahun-akademik', ['action' => 'edit', 'id' => $id]);
        }
        $this->model->update($id, $_POST);
        flash('success', 'Tahun akademik berhasil diperbarui.');
        redirect('admin/tahun-akademik');
    }

    public function delete(): void
    {
        $this->model->delete($this->idParam());
        flash('success', 'Tahun akademik berhasil dihapus.');
        redirect('admin/tahun-akademik');
    }

    private function check(array $d): array
    {
        $errors = $this->validate($d, $this->rules, ['tahun' => 'Tahun akademik']);
        if (!$errors) {
            [$a, $b] = array_map('intval', explode('/', $d['tahun']));
            if ($b !== $a + 1) {
                $errors['tahun'] = 'Tahun akademik harus dua tahun berurutan, misalnya 2026/2027.';
            }
        }
        return $errors;
    }
}
