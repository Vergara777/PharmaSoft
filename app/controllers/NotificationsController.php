<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Config\Database;
use App\Helpers\Auth;

class NotificationsController extends Controller {
    public function alerts(): void {
        if (!Auth::check()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'unauthorized']);
            return;
        }
        $db = Database::getConnection();
        $thr = defined('LOW_STOCK_THRESHOLD') ? LOW_STOCK_THRESHOLD : 5;
        try {
            // Low/Warning stock: include items up to 60 so UI can show rojo(<20) y amarillo(20..60)
            $lowStock = $db->query("SELECT id, sku, name, stock FROM products WHERE status='active' AND stock IS NOT NULL AND stock <= 60 ORDER BY stock ASC, name ASC LIMIT 50")->fetchAll();

            // Expired (includes today)
            $expired = $db->query("SELECT id, sku, name, image, DATE_FORMAT(expires_at,'%Y-%m-%d') AS expires_at FROM products WHERE status='active' AND expires_at IS NOT NULL AND expires_at <= CURDATE() ORDER BY expires_at ASC, name ASC LIMIT 50")->fetchAll();

            // Expiring soon: 1..31 days from today
            $expiring = $db->query("SELECT id, sku, name, image, DATE_FORMAT(expires_at,'%Y-%m-%d') AS expires_at FROM products WHERE status='active' AND expires_at IS NOT NULL AND expires_at > CURDATE() AND expires_at <= DATE_ADD(CURDATE(), INTERVAL 31 DAY) ORDER BY expires_at ASC, name ASC LIMIT 50")->fetchAll();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => true,
                'threshold' => (int)$thr,
                'low_stock' => $lowStock ?: [],
                'expired' => $expired ?: [],
                'expiring' => $expiring ?: [],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'server_error']);
        }
    }
}
