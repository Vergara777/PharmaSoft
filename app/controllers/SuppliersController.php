<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Supplier;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Flash;

class SuppliersController extends Controller {
    private function ensureAdmin(): void { if (!Auth::isAdmin()) { http_response_code(403); exit('Forbidden'); } }

    public function index(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        $suppliers = (new Supplier())->all();
        $this->view('suppliers/index', ['suppliers' => $suppliers, 'title' => 'Proveedores']);
    }

    public function create(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        $this->view('suppliers/create', ['title' => 'Nuevo proveedor']);
    }

    public function store(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $d = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
        ];
        if ($d['name'] === '') { Flash::error('El nombre es obligatorio'); $this->redirect('/suppliers/create'); }
        (new Supplier())->create($d);
        Flash::success('Proveedor creado correctamente', 'Proveedores');
        $this->redirect('/suppliers');
    }

    public function edit($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        $s = (new Supplier())->find((int)$id);
        if (!$s) { http_response_code(404); exit('Proveedor no encontrado'); }
        $this->view('suppliers/edit', ['s' => $s, 'title' => 'Editar proveedor']);
    }

    public function update($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $d = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
        ];
        if ($d['name'] === '') { Flash::error('El nombre es obligatorio'); $this->redirect('/suppliers/edit/' . (int)$id); }
        (new Supplier())->update((int)$id, $d);
        Flash::success('Proveedor actualizado', 'Proveedores');
        $this->redirect('/suppliers');
    }

    public function destroy($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        (new Supplier())->delete((int)$id);
        Flash::success('Proveedor eliminado', 'Proveedores');
        $this->redirect('/suppliers');
    }
}
