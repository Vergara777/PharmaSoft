<?php
namespace App\Config;

// Base URL pointing to /public (adjust if not in /PharmaSoft2.0)
const BASE_URL = '/PharmaSoft2.0/public';

define('BASE_URL', BASE_URL);

define('APP_NAME', 'PharmaSoft');

// Ensure all PHP date/time functions use Colombia timezone
if (!ini_get('date.timezone')) {
    @date_default_timezone_set('America/Bogota');
} else {
    // Force override to avoid server misconfiguration
    @date_default_timezone_set('America/Bogota');
}

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'pharmasoft');
define('DB_USER', 'FILANTROPO');
define('DB_PASS', 'qwertyuiop777');

define('SESSION_NAME', 'pharmasoft_session');

define('UPLOAD_DIR', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads');
if (!is_dir(UPLOAD_DIR)) { @mkdir(UPLOAD_DIR, 0775, true); }

// Inventory thresholds
// Color thresholds for stock badges in products list
define('STOCK_DANGER', 20); // 0..20 => rojo
define('STOCK_WARN', 60);   // 21..60 => amarillo
// Backwards compat for other parts using LOW_STOCK_THRESHOLD
define('LOW_STOCK_THRESHOLD', defined('STOCK_DANGER') ? STOCK_DANGER : 20);
