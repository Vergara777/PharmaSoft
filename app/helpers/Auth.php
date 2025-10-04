<?php
namespace App\Helpers;

use App\Core\View;

class Auth {
    public static function check(): bool { return !empty($_SESSION['user']); }
    public static function user(): ?array { return $_SESSION['user'] ?? null; }
    public static function id(): ?int { return $_SESSION['user']['id'] ?? null; }
    public static function isAdmin(): bool { return (self::user()['role'] ?? '') === 'admin'; }
    
    /**
     * Require a specific role, redirect to login or show error if not authorized
     * @param string|array $roles Role or array of allowed roles
     * @param string $redirect URL to redirect if not logged in (default: '/auth/login')
     * @param string $errorMessage Error message to show if not authorized (default: 'Acceso no autorizado')
     * @return void
     */
    public static function requireRole($roles, string $redirect = '/auth/login', string $errorMessage = 'Acceso no autorizado'): void {
        if (!self::check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirect);
            exit;
        }
        
        $userRole = strtolower(self::role());
        $roles = is_array($roles) ? $roles : [$roles];
        $roles = array_map('strtolower', $roles);
        
        if (!in_array($userRole, $roles, true)) {
            http_response_code(403);
            View::render('error', [
                'title' => 'Acceso denegado',
                'message' => $errorMessage,
                'code' => 403
            ]);
            exit;
        }
    }
    
    public static function role(): string {
        $r = (string) (self::user()['role'] ?? '');
        $r = trim($r);
        // normalize simple accents
        $from = ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'];
        $to   = ['a','e','i','o','u','A','E','I','O','U','n','N'];
        $r = str_replace($from, $to, $r);
        return $r;
    }
    
    public static function isTechnician(): bool {
        $r = strtolower(self::role());
        return in_array($r, ['tecnico','technician','tech'], true);
    }
    
    public static function logout(): void { 
        unset($_SESSION['user']); 
        session_regenerate_id(true); 
    }
}
