<?php
namespace App\Core;

class View {
    public static function e(?string $value): string { 
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); 
    }
    
    /**
     * Render a view file with the given data
     * 
     * @param string $view Name of the view file (without .php extension)
     * @param array $data Associative array of data to pass to the view
     * @return void
     */
    public static function render(string $view, array $data = []): void {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = __DIR__ . "/../views/{$view}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            // If view doesn't exist, show an error
            self::render('error/404', [
                'title' => 'Error 404',
                'message' => 'La vista solicitada no existe.'
            ]);
            return;
        }
        
        // Get the contents of the buffer and clean it
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }
    
    /**
     * Render an error page with the given message and status code
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code (default: 500)
     * @return void
     */
    public static function error(string $message, int $statusCode = 500): void {
        http_response_code($statusCode);
        self::render('error/error', [
            'title' => 'Error ' . $statusCode,
            'message' => $message,
            'code' => $statusCode
        ]);
        exit;
    }
}
