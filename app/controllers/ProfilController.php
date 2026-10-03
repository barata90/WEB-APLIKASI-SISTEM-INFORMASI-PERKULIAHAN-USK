<?php
class ProfilController extends Controller
{
    public array $postActions = ['password'];

    public function index(): void
    {
        $this->render('profil/index', ['title' => 'Profil & Ganti Password', 'user' => Auth::user()]);
    }

    public function password(): void
    {
        $errors = $this->validate($_POST, [
            'password_lama' => 'required',
            'password_baru' => 'required|min:6|max:72',
            'konfirmasi'    => 'required',
        ], ['password_lama' => 'Password lama', 'password_baru' => 'Password baru', 'konfirmasi' => 'Konfirmasi password']);

        $users = new User();
        if (!$errors && !$users->verifyPassword(Auth::id(), $_POST['password_lama'])) {
            $errors['password_lama'] = 'Password lama tidak sesuai.';
        }
        if (!$errors && $_POST['password_baru'] !== $_POST['konfirmasi']) {
            $errors['konfirmasi'] = 'Konfirmasi password tidak sama.';
        }
        if ($errors) {
            $_POST = [];
            $this->backWithErrors($errors, 'profil');
        }
        $users->setPassword(Auth::id(), $_POST['password_baru']);
        flash('success', 'Password berhasil diubah.');
        redirect('profil');
    }
}
