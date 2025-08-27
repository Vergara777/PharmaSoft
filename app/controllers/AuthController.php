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
            // trigger one-time welcome modal on next request
            // 1 = primera vez (en este navegador); 2 = bienvenido de vuelta
            $seen = isset($_COOKIE['ps_welcome_seen']) && $_COOKIE['ps_welcome_seen'] === '1';
            $_SESSION['welcome'] = $seen ? 2 : 1;
            // set persistent cookie (30 días)
            try { setcookie('ps_welcome_seen', '1', time() + 60*60*24*30, '/'); } catch (\Throwable $_) { /* noop */ }
            session_regenerate_id(true);
            // Ensure session data (welcome flag, user) is persisted before redirect
            try { session_write_close(); } catch (\Throwable $_) { }
            $this->redirect('/dashboard');
        } else {
            // Determine specific error
            $payload = ['title' => 'Iniciar sesión', 'email' => $email];
            if (!$user) {
                $payload['errorEmail'] = 'Correo incorrecto';
            } else {
                $payload['errorPassword'] = 'Contraseña incorrecta';
            }
            $this->view('auth/login', $payload);
        }
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/auth/login');
    }
}
