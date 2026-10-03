<?php
class AuthController extends Controller
{
    public array $postActions = ['login', 'logout'];

    public function index(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        $this->render('auth/login', ['title' => 'Login'], 'layouts/guest');
    }

    public function login(): void
    {
        $username = trim((string) input('username', ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            flash('error', 'Username dan password wajib diisi.');
            $_SESSION['_old'] = ['username' => $username];
            redirect('login');
        }

        if (!Auth::attempt($username, $password)) {
            flash('error', 'Username atau password salah, atau akun tidak aktif.');
            $_SESSION['_old'] = ['username' => $username];
            redirect('login');
        }

        flash('success', 'Selamat datang, ' . Auth::user()['nama'] . '.');
        redirect('dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        session_start();
        session_regenerate_id(true);
        flash('success', 'Anda telah keluar dari sistem.');
        redirect('login');
    }
}
