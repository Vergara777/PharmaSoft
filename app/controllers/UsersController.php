<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Flash;

class UsersController extends Controller {
    private function ensureAdmin(): void { if (!Auth::isAdmin()) { http_response_code(403); exit('Forbidden'); } }

    public function index(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        $users = (new User())->all();
        $this->view('users/index', ['users' => $users, 'title' => 'Usuarios']);
    }

    public function create(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        $this->view('users/create', ['title' => 'Crear usuario']);
    }

    public function store(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'] ?? 'tech',
            'password' => (string)($_POST['password'] ?? ''),
        ];
        (new User())->create($data);
        Flash::success('Usuario creado correctamente', 'Usuarios');
        $this->redirect('/users');
    }

    public function edit($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        $user = (new User())->find((int)$id);
        $this->view('users/edit', ['u' => $user, 'title' => 'Editar usuario']);
    }

    public function update($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'] ?? 'tech',
            'password' => (string)($_POST['password'] ?? ''),
        ];
        (new User())->update((int)$id, $data);
        Flash::success('Usuario actualizado', 'Usuarios');
        $this->redirect('/users');
    }

    public function destroy($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $ok = (new User())->delete((int)$id);
        if ($ok) { Flash::success('Usuario eliminado', 'Usuarios'); }
        else { Flash::error('No se pudo eliminar el usuario'); }
        $this->redirect('/users');
    }
}
