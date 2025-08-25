<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Helpers\Security;
use App\Helpers\Auth;

class AuthController extends Controller {
    public function login(): void {
        if (Auth::check()) { $this->redirect('/dashboard'); }
        $this->view('auth/login', ['title' => 'Iniciar sesión']);
    }

    public function doLogin(): void {
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $user = (new User())->findByEmail($email);
        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            session_regenerate_id(true);
            $this->redirect('/dashboard');
        } else {
            $this->view('auth/login', ['error' => 'Credenciales inválidas', 'title' => 'Iniciar sesión']);
        }
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/auth/login');
    }
}
