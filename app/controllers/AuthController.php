<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\UserLoginLog;
use App\Helpers\Security;
use App\Helpers\Auth;

class AuthController extends Controller {
    public function login(): void {
        if (Auth::check()) { $this->redirect('/dashboard'); }
        $this->view('auth/login', ['title' => 'Iniciar sesión']);
    }

    public function doLogin(): void {
        if (!Security::verifyCsrf($_POST['csrf'] ?? '')) { 
            http_response_code(400); 
            exit('CSRF'); 
        }
        
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $loginLog = new UserLoginLog();
        
        // Check for too many failed attempts
        $failedAttempts = $loginLog->getRecentFailedAttempts($ipAddress);
        if ($failedAttempts >= 5) {
            $this->view('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Demasiados intentos fallidos. Por favor, intente más tarde.'
            ]);
            return;
        }
        
        $user = (new User())->findByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Log successful login
            $loginLog->logLogin(
                $user['id'],
                $user['name'],
                $user['role'],
                $ipAddress,
                $userAgent,
                'success'
            );
            
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            
            // Trigger one-time welcome modal on next request
            // 1 = primera vez (en este navegador); 2 = bienvenido de vuelta
            $seen = isset($_COOKIE['ps_welcome_seen']) && $_COOKIE['ps_welcome_seen'] === '1';
            $_SESSION['welcome'] = $seen ? 2 : 1;
            
            // Set persistent cookie (30 días)
            try { 
                setcookie('ps_welcome_seen', '1', time() + 60*60*24*30, '/'); 
            } catch (\Throwable $_) { 
                // No operation
            }
            
            session_regenerate_id(true);
            
            // Ensure session data (welcome flag, user) is persisted before redirect
            try { 
                session_write_close(); 
            } catch (\Throwable $_) { 
                // No operation
            }
            
            $this->redirect('/dashboard');
        } else {
            // Log failed login attempt
            if ($user) {
                $loginLog->logLogin(
                    $user['id'],
                    $user['name'],
                    $user['role'],
                    $ipAddress,
                    $userAgent,
                    'failed'
                );
            } else {
                // Log failed login with unknown user
                $loginLog->logLogin(
                    null,
                    'Unknown User',
                    'guest',
                    $ipAddress,
                    $userAgent,
                    'failed'
                );
            }
            
            // Determine specific error
            $payload = [
                'title' => 'Iniciar sesión', 
                'email' => $email,
                'error' => 'Credenciales inválidas. Intente nuevamente.'
            ];
            
            if (!$user) {
                $payload['errorEmail'] = 'Correo incorrecto';
            } else {
                $payload['errorPassword'] = 'Contraseña incorrecta';
                
                // Show remaining attempts
                $remainingAttempts = 5 - $failedAttempts - 1;
                if ($remainingAttempts > 0) {
                    $payload['error'] .= " (Te quedan {$remainingAttempts} intentos)";
                } else {
                    $payload['error'] = 'Demasiados intentos fallidos. Por favor, intente más tarde.';
                }
            }
            
            $this->view('auth/login', $payload);
        }
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/auth/login');
    }
    
    /**
     * Show login logs (for admin only)
     */
    public function loginLogs(): void {
        Auth::requireRole('admin');
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        
        $loginLog = new UserLoginLog();
        $result = $loginLog->getAllLogs($page, $perPage);
        
        // Prepare data for the view
        $viewData = [
            'title' => 'Registro de Accesos',
            'logs' => $result['data'] ?? [],
            'pagination' => [
                'current' => $result['page'] ?? 1,
                'total' => $result['total_pages'] ?? 1,
                'total_items' => $result['total'] ?? 0
            ]
        ];
        
        // Render the view
        $this->view('auth/login_logs', $viewData);
    }
}
