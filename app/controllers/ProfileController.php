<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Flash;

class ProfileController extends Controller {
    public function index(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $this->view('profile/index', ['user' => Auth::user(), 'title' => 'Perfil']);
    }

    public function uploadAvatar(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) {
            http_response_code(400);
            Flash::error('Token CSRF inválido', 'Error');
            $this->redirect('/profile');
            return;
        }
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            Flash::error('No se subió ningún archivo', 'Error');
            $this->redirect('/profile');
            return;
        }
        $tmp = $_FILES['avatar']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            Flash::error('Formato no permitido. Usa JPG, PNG, GIF o WEBP.', 'Error');
            $this->redirect('/profile');
            return;
        }
        $fname = 'avatar_' . Auth::id() . '_' . time() . '.' . $ext;
        $dest = \UPLOAD_DIR . DIRECTORY_SEPARATOR . $fname;
        if (!move_uploaded_file($tmp, $dest)) {
            Flash::error('No se pudo guardar el archivo', 'Error');
            $this->redirect('/profile');
            return;
        }
        $rel = '/uploads/' . $fname;
        (new \App\Models\User())->updateAvatar(Auth::id(), $rel);
        $_SESSION['user']['avatar'] = $rel;
        $_SESSION['user']['avatar_ver'] = time();
        Flash::success('Avatar actualizado correctamente', 'Perfil');
        $this->redirect('/profile');
    }

    public function update(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) {
            http_response_code(400);
            Flash::error('Token CSRF inválido', 'Error');
            $this->redirect('/profile');
            return;
        }
        $d = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')) ?: null,
            'address' => trim((string)($_POST['address'] ?? '')) ?: null,
            'position' => trim((string)($_POST['position'] ?? '')) ?: null,
            'hire_date' => (string)($_POST['hire_date'] ?? '') ?: null,
            'birth_date' => (string)($_POST['birth_date'] ?? '') ?: null,
            'id_number' => trim((string)($_POST['id_number'] ?? '')) ?: null,
        ];
        // Basic validation
        if ($d['name'] === '' || $d['email'] === '') {
            Flash::error('Nombre y email son obligatorios', 'Perfil');
            $this->redirect('/profile');
            return;
        }
        (new \App\Models\User())->updateProfile(Auth::id(), $d);
        // Refresh session user minimal fields
        $_SESSION['user']['name'] = $d['name'];
        $_SESSION['user']['email'] = $d['email'];
        $_SESSION['user']['phone'] = $d['phone'];
        $_SESSION['user']['address'] = $d['address'];
        $_SESSION['user']['position'] = $d['position'];
        $_SESSION['user']['hire_date'] = $d['hire_date'];
        $_SESSION['user']['birth_date'] = $d['birth_date'];
        $_SESSION['user']['id_number'] = $d['id_number'];
        Flash::success('Perfil actualizado', 'Perfil');
        $this->redirect('/profile');
    }
}
