<?php
namespace App\Core;

class View {
    public static function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}
