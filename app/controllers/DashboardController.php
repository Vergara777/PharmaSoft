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
        // Month and Year totals (importe de ventas)
        $monthSalesTotal = (float)$db->query("SELECT COALESCE(SUM(total),0) s FROM sales WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())")
                                     ->fetchColumn();
        $yearSalesTotal = (float)$db->query("SELECT COALESCE(SUM(total),0) s FROM sales WHERE YEAR(created_at)=YEAR(CURDATE())")
                                    ->fetchColumn();
        // Profit (utilidad) month/year using sale_items snapshot unit_cost when available, fallback to products.cost
        // Safe runtime detection of columns to avoid fatal errors before migrations are applied
        $hasProductCost = false; $hasUnitCost = false;
        try {
            $hasProductCost = (bool)$db->query("SELECT COUNT(*)>0 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='cost'")->fetchColumn();
        } catch (\Throwable $_) {}
        try {
            $hasUnitCost = (bool)$db->query("SELECT COUNT(*)>0 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='sale_items' AND COLUMN_NAME='unit_cost'")->fetchColumn();
        } catch (\Throwable $_) {}

        $monthProfitItems = 0.0; $yearProfitItems = 0.0; $monthProfitLegacy = 0.0; $yearProfitLegacy = 0.0;
        // Part 1: multi-item sales via sale_items (only if we can compute a cost expression)
        if ($hasUnitCost || $hasProductCost) {
            $costExpr = $hasUnitCost && $hasProductCost ? 'COALESCE(si.unit_cost, p.cost, 0)'
                       : ($hasUnitCost ? 'COALESCE(si.unit_cost, 0)'
                       : ($hasProductCost ? 'COALESCE(p.cost, 0)' : '0'));
            $sqlMonthItems = "SELECT COALESCE(SUM(si.qty * (si.unit_price - {$costExpr})), 0)
                               FROM sales s
                               JOIN sale_items si ON si.sale_id = s.id
                               " . ($hasProductCost ? "JOIN products p ON p.id = si.product_id" : "JOIN products p ON p.id = si.product_id") . "
                               WHERE YEAR(s.created_at)=YEAR(CURDATE()) AND MONTH(s.created_at)=MONTH(CURDATE())";
            $sqlYearItems = "SELECT COALESCE(SUM(si.qty * (si.unit_price - {$costExpr})), 0)
                             FROM sales s
                             JOIN sale_items si ON si.sale_id = s.id
                             " . ($hasProductCost ? "JOIN products p ON p.id = si.product_id" : "JOIN products p ON p.id = si.product_id") . "
                             WHERE YEAR(s.created_at)=YEAR(CURDATE())";
            try { $monthProfitItems = (float)$db->query($sqlMonthItems)->fetchColumn(); } catch (\Throwable $_) { $monthProfitItems = 0.0; }
            try { $yearProfitItems = (float)$db->query($sqlYearItems)->fetchColumn(); } catch (\Throwable $_) { $yearProfitItems = 0.0; }
        }
        // Part 2: legacy single-item sales stored directly in sales (product_id, qty, unit_price)
        if ($hasProductCost) {
            $sqlMonthLegacy = "SELECT COALESCE(SUM(s.qty * (s.unit_price - COALESCE(p.cost, 0))), 0)
                               FROM sales s
                               JOIN products p ON p.id = s.product_id
                               WHERE s.product_id IS NOT NULL AND YEAR(s.created_at)=YEAR(CURDATE()) AND MONTH(s.created_at)=MONTH(CURDATE())";
            $sqlYearLegacy  = "SELECT COALESCE(SUM(s.qty * (s.unit_price - COALESCE(p.cost, 0))), 0)
                               FROM sales s
                               JOIN products p ON p.id = s.product_id
                               WHERE s.product_id IS NOT NULL AND YEAR(s.created_at)=YEAR(CURDATE())";
            try { $monthProfitLegacy = (float)$db->query($sqlMonthLegacy)->fetchColumn(); } catch (\Throwable $_) { $monthProfitLegacy = 0.0; }
            try { $yearProfitLegacy  = (float)$db->query($sqlYearLegacy)->fetchColumn(); } catch (\Throwable $_) { $yearProfitLegacy = 0.0; }
        }
        $monthProfit = (float)$monthProfitItems + (float)$monthProfitLegacy;
        $yearProfit  = (float)$yearProfitItems + (float)$yearProfitLegacy;
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
        // Low stock and out-of-stock
        $thr = defined('LOW_STOCK_THRESHOLD') ? LOW_STOCK_THRESHOLD : 5;
        // List including zeros (for cards that already use lowStock count)
        $stmtLowList = $db->prepare("SELECT id, sku, name, stock FROM products WHERE status='active' AND stock <= ? ORDER BY stock ASC, name ASC LIMIT 10");
        $stmtLowList->execute([$thr]);
        $lowStockList = $stmtLowList->fetchAll();
        // Zero stock count and list (separate)
        $zeroStock = (int)$db->query("SELECT COUNT(*) FROM products WHERE status='active' AND stock <= 0")->fetchColumn();
        $zeroStockList = $db->query("SELECT sku, name, stock FROM products WHERE status='active' AND stock <= 0 ORDER BY name ASC LIMIT 5")->fetchAll();
        // Low-but-positive stock count and list
        $stmtLowPosCnt = $db->prepare("SELECT COUNT(*) FROM products WHERE status='active' AND stock > 0 AND stock <= ?");
        $stmtLowPosCnt->execute([$thr]);
        $lowPositiveCount = (int)$stmtLowPosCnt->fetchColumn();
        $stmtLowPos = $db->prepare("SELECT sku, name, stock FROM products WHERE status='active' AND stock > 0 AND stock <= ? ORDER BY stock ASC, name ASC LIMIT 5");
        $stmtLowPos->execute([$thr]);
        $lowPositiveList = $stmtLowPos->fetchAll();
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
            Flash::info('Debug: vencidos='.$expired.' | por vencer='.$expiringSoon, 'Debug Expiración', 6000, 'top-end');
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
            Flash::warning($msg, 'Alerta de Vencimiento', 4000, 'top-end');
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
            Flash::info($msg2, 'Por vencer', 4000, 'top-end');
        }
        // Notify out-of-stock (sin stock)
        if ($zeroStock > 0) {
            $items3 = array_map(function($r){
                $s = isset($r['sku']) ? $r['sku'] : '';
                $n = isset($r['name']) ? $r['name'] : '';
                return trim(($s ? ("[$s] ") : '') . $n);
            }, $zeroStockList ?: []);
            $extra3 = $zeroStock > count($items3) ? (' y +' . ($zeroStock - count($items3)) . ' más') : '';
            $msg3 = 'Tienes ' . $zeroStock . ' producto(s) SIN STOCK.' . (empty($items3) ? '' : (' Ej: ' . implode(', ', $items3))) . $extra3;
            Flash::warning($msg3, 'Inventario agotado', 4000, 'top-end');
        }
        // Notify low stock (se está acabando) excluding zeros
        if ($lowPositiveCount > 0) {
            $items4 = array_map(function($r){
                $s = isset($r['sku']) ? $r['sku'] : '';
                $n = isset($r['name']) ? $r['name'] : '';
                $st = isset($r['stock']) ? (int)$r['stock'] : null;
                return trim(($s ? ("[$s] ") : '') . $n . ($st !== null ? (' (' . $st . ')') : ''));
            }, $lowPositiveList ?: []);
            $extra4 = $lowPositiveCount > count($items4) ? (' y +' . ($lowPositiveCount - count($items4)) . ' más') : '';
            $msg4 = 'Productos con stock bajo (≤ ' . $thr . '): ' . $lowPositiveCount . '.' . (empty($items4) ? '' : (' Ej: ' . implode(', ', $items4))) . $extra4;
            Flash::info($msg4, 'Stock bajo', 4000, 'top-end');
        }
        $this->view('dashboard/index', compact('totalProducts','lowStock','expiring','expired','expiringSoon','todaySalesCount','todaySalesTotal','monthSalesTotal','yearSalesTotal','monthProfit','yearProfit','todaySales','lowStockList','topProducts','heatmap','zeroStock','zeroStockList') + ['title' => 'Dashboard']);
    }
}
