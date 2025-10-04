<?php
namespace App\Core;

class Controller {
    protected $data = [];

    public function __construct() {
        $this->data['title'] = 'PharmaSoft';
    }

    protected function view(string $view, array $data = [], string $layout = 'layouts/main'): void {
        // Combinar los datos del controlador con los datos pasados al método
        $this->data = array_merge($this->data, $data);
        
        // Extraer las variables a variables locales
        extract($this->data, EXTR_SKIP);
        
        // Incluir la vista dentro del layout
        $viewFile = dirname(__DIR__) . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) { 
            echo 'View not found: ' . $viewFile; 
            return; 
        }
        
        // Incluir el layout que a su vez incluirá la vista
        $layoutFile = dirname(__DIR__) . '/views/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo 'Layout not found: ' . $layoutFile;
            return;
        }
        
        include $layoutFile;
    }

    protected function redirect(string $path): void {
        $base = rtrim(\BASE_URL, '/');
        header('Location: ' . $base . $path);
        exit;
    }
    
    /**
     * Format a date as a human-readable time ago string
     * 
     * @param string $datetime MySQL datetime string
     * @return string Human readable time ago string
     */
    protected function timeAgo($datetime) {
        $time = strtotime($datetime);
        $time = time() - $time; // to get the time since that moment
        
        $tokens = array (
            31536000 => 'año',
            2592000 => 'mes',
            604800 => 'semana',
            86400 => 'día',
            3600 => 'hora',
            60 => 'minuto',
            1 => 'segundo'
        );
        
        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return 'Hace ' . $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
        }
        
        return 'Recién';
    }
}
