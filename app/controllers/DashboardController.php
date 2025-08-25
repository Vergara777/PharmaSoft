<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Config\Database;
use App\Helpers\Auth;
use App\Helpers\Flash;

class DashboardController extends Controller {
    public function index(): void {
        if (!Auth::check()) { $this->redirect('/auth/login'); }
        $db = Database::getConnection();
        $totalProducts = (int)$db->query("SELECT COUNT(*) c FROM products WHERE status='active'")->fetchColumn();
        $stmtLow = $db->prepare("SELECT COUNT(*) c FROM products WHERE status='active' AND stock <= ?");
        $stmtLow->execute([defined('LOW_STOCK_THRESHOLD') ? LOW_STOCK_THRESHOLD : 5]);
        $lowStock = (int)$stmtLow->fetchColumn();
        $expiring = (int)$db->query("SELECT COUNT(*) c FROM products WHERE status='active' AND expires_at IS NOT NULL AND expires_at <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
        $expired = (int)$db->query("SELECT COUNT(*) c FROM products WHERE status='active' AND expires_at IS NOT NULL AND expires_at < CURDATE()")->fetchColumn();
        $expiringSoon = (int)$db->query("SELECT COUNT(*) c FROM products WHERE status='active' AND expires_at IS NOT NULL AND expires_at >= CURDATE() AND expires_at <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
        $todaySalesCount = (int)$db->query("SELECT COUNT(*) c FROM sales WHERE DATE(created_at) = CURDATE()")
                                   ->fetchColumn();
        $todaySalesTotal = (float)$db->query("SELECT COALESCE(SUM(total),0) s FROM sales WHERE DATE(created_at) = CURDATE()")
                                     ->fetchColumn();
        // Support legacy single-item and new multi-item sales
        $todaySales = $db->query(
            "SELECT 
                s.id,
                s.qty,
                s.unit_price,
                s.total,
                s.customer_name,
                s.user_name,
                s.created_at,
                (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                (SELECT COALESCE(SUM(si.qty),0) FROM sale_items si WHERE si.sale_id = s.id) AS items_qty,
                (SELECT p2.sku FROM sale_items si2 JOIN products p2 ON p2.id = si2.product_id WHERE si2.sale_id = s.id ORDER BY si2.id ASC LIMIT 1) AS first_sku,
                (SELECT p2.name FROM sale_items si3 JOIN products p2 ON p2.id = si3.product_id WHERE si3.sale_id = s.id ORDER BY si3.id ASC LIMIT 1) AS first_name
             FROM sales s
             WHERE DATE(s.created_at)=CURDATE()
             ORDER BY s.created_at DESC, s.id DESC
             "
        )->fetchAll();
        // Low stock list (top 10)
        $thr = defined('LOW_STOCK_THRESHOLD') ? LOW_STOCK_THRESHOLD : 5;
        $stmtLowList = $db->prepare("SELECT id, sku, name, stock FROM products WHERE status='active' AND stock <= ? ORDER BY stock ASC, name ASC LIMIT 10");
        $stmtLowList->execute([$thr]);
        $lowStockList = $stmtLowList->fetchAll();
        // Top products sold (last 30 days)
        $topProducts = $db->query(
            "SELECT p.id, p.sku, p.name, COALESCE(SUM(si.qty),0) AS qty
             FROM sales s
             JOIN sale_items si ON si.sale_id = s.id
             JOIN products p ON p.id = si.product_id
             WHERE s.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY p.id, p.sku, p.name
             ORDER BY qty DESC, p.name ASC
             LIMIT 10"
        )->fetchAll();
        // Weekly heatmap: totals by day-of-week (1-7) and hour (0-23) for last 7 days
        $heatRows = $db->query(
            "SELECT DAYOFWEEK(s.created_at) AS dow, HOUR(s.created_at) AS hr, COALESCE(SUM(s.total),0) AS total
             FROM sales s
             WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY dow, hr"
        )->fetchAll();
        // Build a 7x24 matrix (1..7 rows, 0..23 cols)
        $heatmap = array_fill(1, 7, array_fill(0, 24, 0.0));
        foreach ($heatRows as $r) {
            $d = (int)($r['dow'] ?? 0); $h = (int)($r['hr'] ?? 0);
            $val = (float)($r['total'] ?? 0);
            if ($d >= 1 && $d <= 7 && $h >= 0 && $h <= 23) { $heatmap[$d][$h] = $val; }
        }
        // Optional debug: force showing counts
        $debugExpiry = isset($_GET['debug_expiry']) && $_GET['debug_expiry'] == '1';
        if ($debugExpiry) {
            Flash::info('Debug: vencidos='.$expired.' | por vencer='.$expiringSoon, 'Debug Expiración', 6000);
        }
        // Notify expired products
        if ($expired > 0) {
            $expiredList = $db->query("SELECT name, sku, DATE_FORMAT(expires_at,'%d/%m/%Y') d FROM products WHERE status='active' AND expires_at IS NOT NULL AND expires_at < CURDATE() ORDER BY expires_at ASC, name ASC LIMIT 5")->fetchAll();
            $items = array_map(function($r){
                $n = isset($r['name']) ? $r['name'] : '';
                $s = isset($r['sku']) ? $r['sku'] : '';
                $d = isset($r['d']) ? $r['d'] : '';
                return trim(($s ? ("[$s] ") : '') . $n . ($d ? (" (".$d.")") : ''));
            }, $expiredList ?: []);
            $extra = $expired > count($items) ? (' y +' . ($expired - count($items)) . ' más') : '';
            $msg = 'Tienes ' . $expired . ' producto(s) VENCIDO(s).' . (empty($items) ? '' : (' Ej: ' . implode(', ', $items))) . $extra;
            Flash::warning($msg, 'Alerta de Vencimiento');
        }
        // Notify expiring soon products
        if ($expiringSoon > 0) {
            $soonList = $db->query("SELECT name, sku, DATE_FORMAT(expires_at,'%d/%m/%Y') d FROM products WHERE status='active' AND expires_at IS NOT NULL AND expires_at >= CURDATE() AND expires_at <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY expires_at ASC, name ASC LIMIT 5")->fetchAll();
            $items2 = array_map(function($r){
                $n = isset($r['name']) ? $r['name'] : '';
                $s = isset($r['sku']) ? $r['sku'] : '';
                $d = isset($r['d']) ? $r['d'] : '';
                return trim(($s ? ("[$s] ") : '') . $n . ($d ? (" (".$d.")") : ''));
            }, $soonList ?: []);
            $extra2 = $expiringSoon > count($items2) ? (' y +' . ($expiringSoon - count($items2)) . ' más') : '';
            $msg2 = 'Tienes ' . $expiringSoon . ' producto(s) por vencer (≤ 30 días).' . (empty($items2) ? '' : (' Ej: ' . implode(', ', $items2))) . $extra2;
            Flash::info($msg2, 'Por vencer');
        }
        $this->view('dashboard/index', compact('totalProducts','lowStock','expiring','expired','expiringSoon','todaySalesCount','todaySalesTotal','todaySales','lowStockList','topProducts','heatmap') + ['title' => 'Dashboard']);
    }
}
