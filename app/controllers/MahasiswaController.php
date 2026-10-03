<?php
class MahasiswaController extends Controller
{
    private Mahasiswa $model;
    private array $rules = [
        'npm'            => 'required|digits:13',
        'nama_mahasiswa' => 'required|max:100',
        'jenis_kelamin'  => 'required|in:L,P',
        'tanggal_lahir'  => 'date',
        'email'          => 'email|max:100',
        'angkatan'       => 'required|int|min:2000|max:2100',
        'id_prodi'       => 'required|int',
        'id_dosen_wali'  => 'int',
    ];
    private array $labels = [
        'npm' => 'NPM', 'nama_mahasiswa' => 'Nama mahasiswa', 'jenis_kelamin' => 'Jenis kelamin',
        'tanggal_lahir' => 'Tanggal lahir', 'id_prodi' => 'Program studi', 'id_dosen_wali' => 'Dosen wali',
    ];

    public function __construct()
    {
        $this->model = new Mahasiswa();
    }

    /** READ */
    public function index(): void
    {
        $cari = (string) input('cari', '');
        $idProdi = (int) input('prodi', 0) ?: null;
        $angkatan = (int) input('angkatan', 0) ?: null;
        $this->render('admin/mahasiswa/index', [
            'title'    => 'Data Mahasiswa',
            'rows'     => $this->model->all($cari, $idProdi, $angkatan),
            'prodi'    => (new Prodi())->options(),
            'angkatanList' => $this->model->angkatanList(),
            'cari'     => $cari,
            'idProdi'  => $idProdi,
            'angkatan' => $angkatan,
        ]);
    }

    public function create(): void
    {
        $this->form(null, 'Tambah Mahasiswa');
    }

    /** CREATE */
    public function store(): void
    {
        $rules = $this->rules + ['password' => 'required|min:6|max:72'];
        if ($errors = $this->validate($_POST, $rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/mahasiswa', ['action' => 'create']);
        }
        $this->model->create($_POST, $_POST['password']);
        flash('success', 'Mahasiswa ' . $_POST['nama_mahasiswa'] . ' berhasil ditambahkan. Akun login: ' . $_POST['npm']);
        redirect('admin/mahasiswa');
    }

    public function edit(): void
    {
        $this->form($this->model->find($this->idParam()) ?? abort(404), 'Ubah Mahasiswa');
    }

    /** UPDATE */
    public function update(): void
    {
        $id = $this->idParam();
        if ($errors = $this->validate($_POST, $this->rules, $this->labels)) {
            $this->backWithErrors($errors, 'admin/mahasiswa', ['action' => 'edit', 'id' => $id]);
        }
        $this->model->update($id, $_POST);
        flash('success', 'Data mahasiswa berhasil diperbarui.');
        redirect('admin/mahasiswa');
    }

    /** DELETE */
    public function delete(): void
    {
        $this->model->delete($this->idParam());
        flash('success', 'Mahasiswa berhasil dihapus.');
        redirect('admin/mahasiswa');
    }

    private function form(?array $row, string $title): void
    {
        $this->render('admin/mahasiswa/form', [
            'title' => $title,
            'row'   => $row,
            'prodi' => (new Prodi())->options(),
            'dosen' => (new Dosen())->options(),
        ]);
    }
}
