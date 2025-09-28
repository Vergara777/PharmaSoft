<?php
namespace App\Models;

use App\Core\Model;
use App\Helpers\Audit;
use App\Helpers\Auth;
use PDO;

class Sale extends Model {
    public function all(): array {
        $sql = 'SELECT 
                    s.id,
                    s.product_id,
                    p.sku,
                    p.name,
                    s.qty,
                    s.unit_price,
                    s.total,
                    s.customer_name,
                    s.customer_phone,
                    s.customer_email,
                    s.user_name,
                    s.user_role,
                    s.created_at,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                    (SELECT COALESCE(SUM(si.qty),0) FROM sale_items si WHERE si.sale_id = s.id) AS items_qty,
                    (SELECT p2.sku FROM sale_items si2 JOIN products p2 ON p2.id = si2.product_id WHERE si2.sale_id = s.id ORDER BY si2.id ASC LIMIT 1) AS first_sku,
                    (SELECT p2.name FROM sale_items si3 JOIN products p2 ON p2.id = si3.product_id WHERE si3.sale_id = s.id ORDER BY si3.id ASC LIMIT 1) AS first_name
                FROM sales s 
                LEFT JOIN products p ON p.id = s.product_id
                ORDER BY s.id ASC';
        return $this->db->query($sql)->fetchAll();
    }

    /** Count sales between dates inclusive (Y-m-d). */
    public function countByDate(string $from, string $to): int {
        $sql = 'SELECT COUNT(*) FROM sales s WHERE DATE(s.created_at) BETWEEN :f AND :t';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':f', $from);
        $stmt->bindValue(':t', $to);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /** Paginated list of sales between dates inclusive, ordered ASC by id. */
    public function byDatePaginated(string $from, string $to, int $limit, int $offset): array {
        if ($limit < 9) { $limit = 9; }
        if ($offset < 0) { $offset = 0; }
        $sql = 'SELECT 
                    s.id,
                    s.product_id,
                    p.sku,
                    p.name,
                    s.qty,
                    s.unit_price,
                    s.total,
                    s.customer_name,
                    s.customer_phone,
                    s.customer_email,
                    s.user_name,
                    s.user_role,
                    s.created_at,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                    (SELECT COALESCE(SUM(si.qty),0) FROM sale_items si WHERE si.sale_id = s.id) AS items_qty,
                    (SELECT p2.sku FROM sale_items si2 JOIN products p2 ON p2.id = si2.product_id WHERE si2.sale_id = s.id ORDER BY si2.id ASC LIMIT 1) AS first_sku,
                    (SELECT p2.name FROM sale_items si3 JOIN products p2 ON p2.id = si3.product_id WHERE si3.sale_id = s.id ORDER BY si3.id ASC LIMIT 1) AS first_name
                FROM sales s 
                LEFT JOIN products p ON p.id = s.product_id
                WHERE DATE(s.created_at) BETWEEN :f AND :t
                ORDER BY s.id ASC
                LIMIT :lim OFFSET :off';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':f', $from);
        $stmt->bindValue(':t', $to);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Count today's sales (date filter). */
    public function countToday(): int {
        $stmt = $this->db->query('SELECT COUNT(*) FROM sales s WHERE DATE(s.created_at) = CURDATE()');
        return (int)$stmt->fetchColumn();
    }

    /** Paginated list of today's sales ordered DESC by id. */
    public function todayPaginated(int $limit, int $offset): array {
        if ($limit < 9) { $limit = 9; }
        if ($offset < 0) { $offset = 0; }
        $sql = 'SELECT 
                    s.id,
                    s.product_id,
                    p.sku,
                    p.name,
                    s.qty,
                    s.unit_price,
                    s.total,
                    s.customer_name,
                    s.customer_phone,
                    s.customer_email,
                    s.user_name,
                    s.user_role,
                    s.created_at,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                    (SELECT COALESCE(SUM(si.qty),0) FROM sale_items si WHERE si.sale_id = s.id) AS items_qty,
                    (SELECT p2.sku FROM sale_items si2 JOIN products p2 ON p2.id = si2.product_id WHERE si2.sale_id = s.id ORDER BY si2.id ASC LIMIT 1) AS first_sku,
                    (SELECT p2.name FROM sale_items si3 JOIN products p2 ON p2.id = si3.product_id WHERE si3.sale_id = s.id ORDER BY si3.id ASC LIMIT 1) AS first_name
                FROM sales s 
                LEFT JOIN products p ON p.id = s.product_id
                WHERE DATE(s.created_at) = CURDATE()
                ORDER BY s.id ASC
                LIMIT :lim OFFSET :off';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Count all sales. */
    public function countAll(): int {
        $stmt = $this->db->query('SELECT COUNT(*) FROM sales');
        return (int)$stmt->fetchColumn();
    }

    /** Paginated list of all sales ordered DESC by id. */
    public function allPaginated(int $limit, int $offset): array {
        if ($limit < 9) { $limit = 9; }
        if ($offset < 0) { $offset = 0; }
        $sql = 'SELECT 
                    s.id,
                    s.product_id,
                    p.sku,
                    p.name,
                    s.qty,
                    s.unit_price,
                    s.total,
                    s.customer_name,
                    s.customer_phone,
                    s.customer_email,
                    s.user_name,
                    s.user_role,
                    s.created_at,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                    (SELECT COALESCE(SUM(si.qty),0) FROM sale_items si WHERE si.sale_id = s.id) AS items_qty,
                    (SELECT p2.sku FROM sale_items si2 JOIN products p2 ON p2.id = si2.product_id WHERE si2.sale_id = s.id ORDER BY si2.id ASC LIMIT 1) AS first_sku,
                    (SELECT p2.name FROM sale_items si3 JOIN products p2 ON p2.id = si3.product_id WHERE si3.sale_id = s.id ORDER BY si3.id ASC LIMIT 1) AS first_name
                FROM sales s 
                LEFT JOIN products p ON p.id = s.product_id
                ORDER BY s.id ASC
                LIMIT :lim OFFSET :off';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** List all sales (no date filter) ordered ASC by id */
    public function allAsc(): array {
        $sql = 'SELECT 
                    s.id,
                    s.product_id,
                    p.sku,
                    p.name,
                    s.qty,
                    s.unit_price,
                    s.total,
                    s.customer_name,
                    s.customer_phone,
                    s.customer_email,
                    s.user_name,
                    s.user_role,
                    s.created_at,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                    (SELECT COALESCE(SUM(si.qty),0) FROM sale_items si WHERE si.sale_id = s.id) AS items_qty,
                    (SELECT p2.sku FROM sale_items si2 JOIN products p2 ON p2.id = si2.product_id WHERE si2.sale_id = s.id ORDER BY si2.id ASC LIMIT 1) AS first_sku,
                    (SELECT p2.name FROM sale_items si3 JOIN products p2 ON p2.id = si3.product_id WHERE si3.sale_id = s.id ORDER BY si3.id ASC LIMIT 1) AS first_name
                FROM sales s 
                LEFT JOIN products p ON p.id = s.product_id
                ORDER BY s.id ASC';
        return $this->db->query($sql)->fetchAll();
    }

    /** List all sales filtered by id (no date filter), ordered ASC */
    public function allById(int $id): array {
        $sql = 'SELECT 
                    s.id,
                    s.product_id,
                    p.sku,
                    p.name,
                    s.qty,
                    s.unit_price,
                    s.total,
                    s.customer_name,
                    s.customer_phone,
                    s.customer_email,
                    s.user_name,
                    s.user_role,
                    s.created_at,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                    (SELECT COALESCE(SUM(si.qty),0) FROM sale_items si WHERE si.sale_id = s.id) AS items_qty,
                    (SELECT p2.sku FROM sale_items si2 JOIN products p2 ON p2.id = si2.product_id WHERE si2.sale_id = s.id ORDER BY si2.id ASC LIMIT 1) AS first_sku,
                    (SELECT p2.name FROM sale_items si3 JOIN products p2 ON p2.id = si3.product_id WHERE si3.sale_id = s.id ORDER BY si3.id ASC LIMIT 1) AS first_name
                FROM sales s 
                LEFT JOIN products p ON p.id = s.product_id
                WHERE s.id = ?
                ORDER BY s.id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function today(): array {
        $sql = 'SELECT 
                    s.id,
                    s.product_id,
                    p.sku,
                    p.name,
                    s.qty,
                    s.unit_price,
                    s.total,
                    s.customer_name,
                    s.customer_phone,
                    s.customer_email,
                    s.user_name,
                    s.user_role,
                    s.created_at,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                    (SELECT COALESCE(SUM(si.qty),0) FROM sale_items si WHERE si.sale_id = s.id) AS items_qty,
                    (SELECT p2.sku FROM sale_items si2 JOIN products p2 ON p2.id = si2.product_id WHERE si2.sale_id = s.id ORDER BY si2.id ASC LIMIT 1) AS first_sku,
                    (SELECT p2.name FROM sale_items si3 JOIN products p2 ON p2.id = si3.product_id WHERE si3.sale_id = s.id ORDER BY si3.id ASC LIMIT 1) AS first_name
                FROM sales s 
                LEFT JOIN products p ON p.id = s.product_id
                WHERE DATE(s.created_at) = CURDATE()
                ORDER BY s.id ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function todayById(int $id): array {
        $sql = 'SELECT 
                    s.id,
                    s.product_id,
                    p.sku,
                    p.name,
                    s.qty,
                    s.unit_price,
                    s.total,
                    s.customer_name,
                    s.customer_phone,
                    s.customer_email,
                    s.user_name,
                    s.user_role,
                    s.created_at,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                    (SELECT COALESCE(SUM(si.qty),0) FROM sale_items si WHERE si.sale_id = s.id) AS items_qty,
                    (SELECT p2.sku FROM sale_items si2 JOIN products p2 ON p2.id = si2.product_id WHERE si2.sale_id = s.id ORDER BY si2.id ASC LIMIT 1) AS first_sku,
                    (SELECT p2.name FROM sale_items si3 JOIN products p2 ON p2.id = si3.product_id WHERE si3.sale_id = s.id ORDER BY si3.id ASC LIMIT 1) AS first_name
                FROM sales s 
                LEFT JOIN products p ON p.id = s.product_id
                WHERE DATE(s.created_at) = CURDATE() AND s.id = ?
                ORDER BY s.id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function create(int $productId, int $qty, float $unitPrice, ?string $customerName = null, ?string $customerPhone = null, ?string $customerEmail = null): int {
        $this->db->beginTransaction();
        try {
            // Check stock
            $stmt = $this->db->prepare('SELECT stock, expires_at, sku, name FROM products WHERE id = ? FOR UPDATE');
            $stmt->execute([$productId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) { throw new \RuntimeException('Producto no existe'); }
            $stock = (int)$row['stock'];
            $expiresAt = $row['expires_at'] ?? null;
            if ($qty <= 0) { throw new \RuntimeException('Cantidad inválida'); }
            if ($unitPrice < 0) { throw new \RuntimeException('Precio inválido'); }
            if ($stock < $qty) {
                $sku = trim((string)($row['sku'] ?? ''));
                $name = trim((string)($row['name'] ?? ''));
                $label = $sku !== '' ? ($sku . ' - ' . $name) : ($name !== '' ? $name : ('ID ' . $productId));
                throw new \RuntimeException('Stock insuficiente para ' . $label . '. Disponible: ' . $stock . ', solicitado: ' . $qty . '.');
            }
            // Block expired products
            if (!empty($expiresAt)) {
                // Compare as dates (Y-m-d)
                $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
                if ($expiresAt < $today) {
                    $sku = trim((string)($row['sku'] ?? ''));
                    $name = trim((string)($row['name'] ?? ''));
                    $label = $sku !== '' ? ($sku . ' - ' . $name) : ($name !== '' ? $name : ('ID ' . $productId));
                    throw new \RuntimeException('Producto vencido: ' . $label . '. No puede venderse.');
                }
            }

            // Resolve current user
            $u = Auth::user();
            $uid = $u['id'] ?? null;
            $urole = $u['role'] ?? null;
            $uname = $u['name'] ?? null;

            // Insert sale
            $total = $qty * $unitPrice;
            $ins = $this->db->prepare('INSERT INTO sales (product_id, qty, unit_price, total, customer_name, customer_phone, customer_email, user_id, user_role, user_name) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $ins->execute([$productId, $qty, $unitPrice, $total, $customerName, $customerPhone, $customerEmail, $uid, $urole, $uname]);
            $saleId = (int)$this->db->lastInsertId();
            
            // Decrement stock
            $upd = $this->db->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            $upd->execute([$qty, $productId]);

            $this->db->commit();
            // Audit log (sale created - legacy single item)
            try {
                Audit::log('sale', (int)$saleId, 'create', [
                    'mode' => 'single',
                    'product_id' => (int)$productId,
                    'qty' => (int)$qty,
                    'unit_price' => (float)$unitPrice,
                    'total' => (float)$total,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'customer_email' => $customerEmail,
                ]);
            } catch (\Throwable $ex) { /* silent */ }
            return $saleId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Find sale header and its items (supports legacy single-item sales).
     */
    public function findByIdWithItems(int $id): ?array {
        // Header
        $h = $this->db->prepare('SELECT id, customer_name, customer_phone, customer_email, total, created_at, user_name, user_role FROM sales WHERE id = ?');
        $h->execute([$id]);
        $sale = $h->fetch(PDO::FETCH_ASSOC);
        if (!$sale) return null;
        // Items
        $it = $this->db->prepare('SELECT si.product_id, si.qty, si.unit_price, si.line_total, p.sku, p.name
                                   FROM sale_items si JOIN products p ON p.id = si.product_id
                                   WHERE si.sale_id = ?');
        $it->execute([$id]);
        $items = $it->fetchAll(PDO::FETCH_ASSOC);
        if (!$items) {
            // Legacy single-item fallback
            $legacy = $this->db->prepare('SELECT s.product_id, s.qty, s.unit_price, s.total AS line_total, p.sku, p.name
                                          FROM sales s JOIN products p ON p.id = s.product_id
                                          WHERE s.id = ?');
            $legacy->execute([$id]);
            $row = $legacy->fetch(PDO::FETCH_ASSOC);
            if ($row) { $items = [$row]; if (empty($sale['total'])) { $sale['total'] = $row['line_total']; } }
        }
        $sale['id'] = $id;
        $sale['items'] = $items;
        return $sale;
    }

    public function createCart(array $items, ?string $customerName = null, ?string $customerPhone = null, ?string $customerEmail = null): int {
        if (empty($items)) { throw new \InvalidArgumentException('El carrito está vacío'); }
        // Normalize and validate
        $norm = [];
        foreach ($items as $it) {
            $pid = (int)($it['product_id'] ?? 0);
            $qty = (int)($it['qty'] ?? 0);
            $price = (float)($it['unit_price'] ?? 0);
            if ($pid <= 0 || $qty <= 0 || $price < 0) { throw new \RuntimeException('Ítem de carrito inválido'); }
            $norm[] = ['product_id' => $pid, 'qty' => $qty, 'unit_price' => $price];
        }

        $this->db->beginTransaction();
        try {
            // Resolve current user
            $u = Auth::user();
            $uid = $u['id'] ?? null;
            $urole = $u['role'] ?? null;
            $uname = $u['name'] ?? null;
            // Insert header first with total 0
            $insSale = $this->db->prepare('INSERT INTO sales (product_id, qty, unit_price, total, customer_name, customer_phone, customer_email, user_id, user_role, user_name) VALUES (NULL, NULL, NULL, 0, ?, ?, ?, ?, ?, ?)');
            $insSale->execute([$customerName, $customerPhone, $customerEmail, $uid, $urole, $uname]);
            $saleId = (int)$this->db->lastInsertId();

            $total = 0.0;
            $insItem = $this->db->prepare('INSERT INTO sale_items (sale_id, product_id, qty, unit_price, unit_cost, line_total) VALUES (?,?,?,?,?,?)');
            $updStock = $this->db->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            $selProd = $this->db->prepare('SELECT stock, expires_at, cost, sku, name FROM products WHERE id = ? FOR UPDATE');

            foreach ($norm as $it) {
                $selProd->execute([$it['product_id']]);
                $row = $selProd->fetch(PDO::FETCH_ASSOC);
                if (!$row) { throw new \RuntimeException('Producto no existe'); }
                $stock = (int)$row['stock'];
                $expiresAt = $row['expires_at'] ?? null;
                $unitCost = isset($row['cost']) ? (float)$row['cost'] : 0.0;
                if ($stock < $it['qty']) {
                    $sku = trim((string)($row['sku'] ?? ''));
                    $name = trim((string)($row['name'] ?? ''));
                    $label = $sku !== '' ? ($sku . ' - ' . $name) : ($name !== '' ? $name : ('ID ' . (int)$it['product_id']));
                    throw new \RuntimeException('Stock insuficiente para ' . $label . '. Disponible: ' . $stock . ', solicitado: ' . (int)$it['qty'] . '.');
                }
                if (!empty($expiresAt)) {
                    $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
                    if ($expiresAt < $today) {
                        $sku = trim((string)($row['sku'] ?? ''));
                        $name = trim((string)($row['name'] ?? ''));
                        $label = $sku !== '' ? ($sku . ' - ' . $name) : ($name !== '' ? $name : ('ID ' . (int)$it['product_id']));
                        throw new \RuntimeException('Producto vencido: ' . $label . '. No puede venderse.');
                    }
                }

                $line = $it['qty'] * $it['unit_price'];
                $insItem->execute([$saleId, $it['product_id'], $it['qty'], $it['unit_price'], $unitCost, $line]);
                $updStock->execute([$it['qty'], $it['product_id']]);
                $total += $line;
            }

            // Update sale total
            $updSale = $this->db->prepare('UPDATE sales SET total = ? WHERE id = ?');
            $updSale->execute([$total, $saleId]);

            $this->db->commit();
            // Audit log (sale created - cart)
            try {
                Audit::log('sale', (int)$saleId, 'create', [
                    'mode' => 'cart',
                    'items' => $norm,
                    'total' => (float)$total,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'customer_email' => $customerEmail,
                ]);
            } catch (\Throwable $ex) { /* silent */ }
            return $saleId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findByIdWithProduct(int $id): ?array {
        $sql = 'SELECT s.id, s.product_id, p.sku, p.name, p.description, s.qty, s.unit_price, s.total, s.customer_name, s.customer_phone, s.created_at
                FROM sales s JOIN products p ON p.id = s.product_id
                WHERE s.id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
