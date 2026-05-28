<?php
class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) { $this->redirect('dashboard'); return; }
        $this->render('auth/login', ['error' => null], '');
    }

    public function login(): void
    {
        $username = trim((string)input('username', ''));
        $password = (string)input('password', '');
        if ($username === '' || $password === '') {
            $this->render('auth/login', ['error' => 'Please enter both username and password.'], '');
            return;
        }
        if (Auth::attempt($username, $password)) {
            flash('success', 'Welcome back, ' . Auth::user()['full_name'] . '!');
            $this->redirect('dashboard');
            return;
        }
        $this->render('auth/login', ['error' => 'Invalid credentials or account inactive.'], '');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('login');
    }
}
