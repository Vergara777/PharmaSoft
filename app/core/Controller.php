<?php
namespace App\Core;

class Controller {
    protected function view(string $view, array $data = [], string $layout = 'layouts/main'): void {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__) . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) { echo 'View not found'; return; }
        include dirname(__DIR__) . '/views/' . $layout . '.php';
    }

    protected function redirect(string $path): void {
        $base = rtrim(\BASE_URL, '/');
        header('Location: ' . $base . $path);
        exit;
    }
}
