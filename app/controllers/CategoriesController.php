<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Flash;

class CategoriesController extends Controller {
    private function ensureAdmin(): void { if (!Auth::isAdmin()) { http_response_code(403); exit('Forbidden'); } }

    public function index(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        $categories = (new Category())->all();
        $this->view('categories/index', ['categories' => $categories, 'title' => 'Categorías']);
    }

    public function create(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        $this->view('categories/create', ['title' => 'Nueva categoría']);
    }

    public function store(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $d = [ 'name' => trim($_POST['name'] ?? '') ];
        if ($d['name'] === '') { Flash::error('El nombre es obligatorio'); $this->redirect('/categories/create'); }
        (new Category())->create($d);
        Flash::success('Categoría creada correctamente', 'Categorías');
        $this->redirect('/categories');
    }

    public function edit($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        $c = (new Category())->find((int)$id);
        if (!$c) { http_response_code(404); exit('Categoría no encontrada'); }
        $this->view('categories/edit', ['c' => $c, 'title' => 'Editar categoría']);
    }

    public function update($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        $d = [ 'name' => trim($_POST['name'] ?? '') ];
        if ($d['name'] === '') { Flash::error('El nombre es obligatorio'); $this->redirect('/categories/edit/' . (int)$id); }
        (new Category())->update((int)$id, $d);
        Flash::success('Categoría actualizada', 'Categorías');
        $this->redirect('/categories');
    }

    public function destroy($id): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->ensureAdmin();
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { http_response_code(400); exit('CSRF'); }
        (new Category())->delete((int)$id);
        Flash::success('Categoría eliminada', 'Categorías');
        $this->redirect('/categories');
    }
}
