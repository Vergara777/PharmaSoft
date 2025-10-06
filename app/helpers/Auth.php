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
        // Log the logout time if user is logged in and has a login ID
        if (!empty($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
            $loginId = $_SESSION['login_id'] ?? null;
            
            if ($loginId) {
                error_log("Intentando registrar cierre de sesión para usuario $userId, login_id: $loginId");
                try {
                    $loginLog = new \App\Models\UserLoginLog();
                    $result = $loginLog->logLogout($loginId);
                    
                    if ($result) {
                        error_log("Cierre de sesión registrado correctamente para usuario $userId, login_id: $loginId");
                    } else {
                        error_log("No se pudo registrar el cierre de sesión para usuario $userId, login_id: $loginId");
                        
                        // Intentar obtener el último login_id si el actual falla
                        $lastLoginId = $loginLog->getLastLoginId($userId);
                        if ($lastLoginId && $lastLoginId != $loginId) {
                            error_log("Intentando con el último login_id conocido: $lastLoginId");
                            $loginLog->logLogout($lastLoginId);
                        }
                    }
                } catch (\Exception $e) {
                    error_log('Error al registrar cierre de sesión: ' . $e->getMessage());
                }
            } else {
                error_log("No se encontró login_id en la sesión para el usuario $userId");
                
                // Intentar obtener el último login_id si no está en la sesión
                try {
                    $loginLog = new \App\Models\UserLoginLog();
                    $lastLoginId = $loginLog->getLastLoginId($userId);
                    if ($lastLoginId) {
                        error_log("Usando último login_id conocido: $lastLoginId");
                        $loginLog->logLogout($lastLoginId);
                    }
                } catch (\Exception $e) {
                    error_log('Error al obtener último login_id: ' . $e->getMessage());
                }
            }
        } else {
            error_log("No se pudo obtener el ID de usuario de la sesión");
        }
        
        // Limpiar datos de sesión
        unset($_SESSION['user']);
        unset($_SESSION['login_id']);
        session_regenerate_id(true);
    }
}
