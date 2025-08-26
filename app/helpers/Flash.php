<?php
namespace App\Helpers;

class Flash {
    private static function ensure(): void {
        if (!isset($_SESSION)) { session_start(); }
        if (!isset($_SESSION['__flash'])) { $_SESSION['__flash'] = []; }
    }

    /**
     * Add a flash message
     * @param string $type one of success,error,warning,info,question
     * @param string $message main text
     * @param string $title optional title for the toast
     * @param int    $timer optional auto-close in ms (0 = default)
     * @param string $position optional SweetAlert2 position (e.g., 'center','top-end')
     */
    public static function add(string $type, string $message, string $title = '', int $timer = 0, string $position = ''): void {
        self::ensure();
        $_SESSION['__flash'][] = [
            'type' => in_array($type, ['success','error','warning','info','question'], true) ? $type : 'info',
            'message' => $message,
            'title' => $title,
            'timer' => $timer,
            'position' => $position,
        ];
    }

    public static function success(string $message, string $title = '', int $timer = 0, string $position = ''): void { self::add('success', $message, $title, $timer, $position); }
    public static function error(string $message, string $title = '', int $timer = 0, string $position = ''): void { self::add('error', $message, $title, $timer, $position); }
    public static function warning(string $message, string $title = '', int $timer = 0, string $position = ''): void { self::add('warning', $message, $title, $timer, $position); }
    public static function info(string $message, string $title = '', int $timer = 0, string $position = ''): void { self::add('info', $message, $title, $timer, $position); }

    public static function popAll(): array {
        self::ensure();
        $msgs = $_SESSION['__flash'] ?? [];
        $_SESSION['__flash'] = [];
        return $msgs;
    }
}
