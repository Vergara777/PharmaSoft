<?php
namespace App\Helpers;

class Auth {
    public static function check(): bool { return !empty($_SESSION['user']); }
    public static function user(): ?array { return $_SESSION['user'] ?? null; }
    public static function id(): ?int { return $_SESSION['user']['id'] ?? null; }
    public static function isAdmin(): bool { return (self::user()['role'] ?? '') === 'admin'; }
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
    public static function logout(): void { unset($_SESSION['user']); session_regenerate_id(true); }
}
