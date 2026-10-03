<?php
class PenggunaController extends Controller
{
    public array $postActions = ['store', 'reset', 'toggle'];
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function index(): void
    {
        $role = (string) input('role', '');
        $role = in_array($role, ['admin', 'dosen', 'mahasiswa'], true) ? $role : '';
        $this->render('admin/pengguna/index', [
            'title' => 'Manajemen Pengguna',
            'rows'  => $this->model->all($role),
            'role'  => $role,
        ]);
    }

    /** Menambah akun administrator baru (akun dosen/mahasiswa dibuat dari menu masing-masing). */
    public function store(): void
    {
        $errors = $this->validate($_POST, [
            'username' => 'required|min:4|max:30|regex:/^[a-zA-Z0-9_.]+$/',
            'password' => 'required|min:6|max:72',
        ]);
        if ($errors) {
            $this->backWithErrors($errors, 'admin/pengguna');
        }
        $this->model->createAdmin($_POST['username'], $_POST['password']);
        flash('success', 'Akun administrator berhasil dibuat.');
        redirect('admin/pengguna');
    }

    public function reset(): void
    {
        $id = $this->idParam();
        $user = $this->model->find($id) ?? abort(404);
        // Password direset menjadi username (NIDN/NPM); pengguna disarankan segera menggantinya.
        $this->model->setPassword($id, $user['username']);
        flash('success', "Password akun {$user['username']} direset menjadi sama dengan username.");
        redirect('admin/pengguna');
    }

    public function toggle(): void
    {
        $id = $this->idParam();
        if ($id === Auth::id()) {
            flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
            redirect('admin/pengguna');
        }
        $this->model->toggleAktif($id);
        flash('success', 'Status akun berhasil diubah.');
        redirect('admin/pengguna');
    }
}
