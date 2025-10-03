<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Product extends Model {
    public function all(): array {
        // Include stock so sales create view can validate availability correctly
        return $this->db->query("SELECT id, sku, name, description, image, stock, price, expires_at, status, category_id, supplier_id, shelf, `row`, `position` FROM products WHERE status = 'active' ORDER BY shelf ASC, `row` ASC, `position` ASC, name ASC")->fetchAll();
    }
    
    /**
     * Obtiene todos los estantes únicos
     */
    public function getShelves(): array {
        return $this->db->query("SELECT DISTINCT shelf FROM products WHERE shelf IS NOT NULL AND shelf != '' ORDER BY shelf")->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Obtiene los productos por estante y fila
     */
    public function getProductsByLocation(?string $shelf = null, ?int $row = null): array {
        $sql = "SELECT * FROM products WHERE status = 'active'";
        $params = [];
        
        if ($shelf !== null) {
            $sql .= " AND shelf = :shelf";
            $params[':shelf'] = $shelf;
            
            if ($row !== null) {
                $sql .= " AND `row` = :row";
                $params[':row'] = $row;
            }
        }
        
        $sql .= " ORDER BY shelf ASC, `row` ASC, `position` ASC, name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function search(string $q = '', ?int $categoryId = null): array {
        if ($q === '') {
            if ($categoryId !== null) {
                $st = $this->db->prepare("SELECT * FROM products WHERE status = 'active' AND category_id = :cid ORDER BY display_no ASC, id ASC");
                $st->bindValue(':cid', $categoryId, PDO::PARAM_INT);
                $st->execute();
                return $st->fetchAll();
            }
            $stmt = $this->db->query("SELECT * FROM products WHERE status = 'active' ORDER BY display_no ASC, id ASC");
            return $stmt->fetchAll();
        }
        $trim = trim($q);
        // If the query is a positive integer, prioritize exact ID match first
        if (ctype_digit($trim)) {
            $id = (int)$trim;
            $sql = "SELECT * FROM products WHERE id = :id AND status = 'active'" . ($categoryId !== null ? " AND category_id = :cid" : "") . " ORDER BY id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if ($categoryId !== null) { $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT); }
            $stmt->execute();
            return $stmt->fetchAll();
        }
        // Default: search by name or SKU
        $like = "%$trim%";
        $sql = "SELECT * FROM products WHERE status = 'active' AND (name LIKE :like OR sku LIKE :like2)" . ($categoryId !== null ? " AND category_id = :cid" : "") . " ORDER BY display_no ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':like2', $like, PDO::PARAM_STR);
        if ($categoryId !== null) { $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT); }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Filtered search with optional expiry and stock filters. Does not affect existing methods.
     * - $expiryDays: if set (e.g., 30 or 60), include products with expires_at <= CURDATE() + INTERVAL $expiryDays DAY
     * - $stockFilter: 'low' filters by stock > 0 AND stock <= $lowStockThreshold
     */
    public function searchFilteredPaginated(string $q, int $limit, int $offset, ?int $categoryId = null, ?int $expiryDays = null, ?string $stockFilter = null, ?int $lowStockThreshold = null): array {
        if ($limit < 9) { $limit = 9; }
        if ($offset < 0) { $offset = 0; }

        $where = ["status = 'active'"];
        $bind = [];

        if ($categoryId !== null) { $where[] = 'category_id = :cid'; $bind[':cid'] = [$categoryId, PDO::PARAM_INT]; }
        if ($expiryDays !== null && $expiryDays > 0) {
            $where[] = 'expires_at IS NOT NULL AND expires_at <= DATE_ADD(CURDATE(), INTERVAL :ed DAY)';
            $bind[':ed'] = [$expiryDays, PDO::PARAM_INT];
        }
        if ($stockFilter === 'low') {
            $thr = ($lowStockThreshold !== null && $lowStockThreshold > 0) ? $lowStockThreshold : 5;
            $where[] = 'stock <= :thr';
            $bind[':thr'] = [$thr, PDO::PARAM_INT];
        }

        $whereSql = implode(' AND ', $where);

        $trim = trim($q);
        if ($trim === '') {
            $sql = "SELECT * FROM products WHERE $whereSql ORDER BY display_no ASC, id ASC LIMIT :lim OFFSET :off";
            $stmt = $this->db->prepare($sql);
            foreach ($bind as $k => $v) { $stmt->bindValue($k, $v[0], $v[1]); }
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        if (ctype_digit($trim)) {
            $id = (int)$trim;
            $sql = "SELECT * FROM products WHERE id = :id AND $whereSql ORDER BY id ASC LIMIT :lim OFFSET :off";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            foreach ($bind as $k => $v) { $stmt->bindValue($k, $v[0], $v[1]); }
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like = "%$trim%";
        $sql = "SELECT * FROM products WHERE (name LIKE :like OR sku LIKE :like2) AND $whereSql ORDER BY display_no ASC, id ASC LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':like2', $like, PDO::PARAM_STR);
        foreach ($bind as $k => $v) { $stmt->bindValue($k, $v[0], $v[1]); }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Count for filtered search (same semantics as searchFilteredPaginated). */
    public function countFiltered(string $q = '', ?int $categoryId = null, ?int $expiryDays = null, ?string $stockFilter = null, ?int $lowStockThreshold = null): int {
        $where = ["status = 'active'"];
        $bind = [];
        if ($categoryId !== null) { $where[] = 'category_id = :cid'; $bind[':cid'] = [$categoryId, PDO::PARAM_INT]; }
        if ($expiryDays !== null && $expiryDays > 0) {
            $where[] = 'expires_at IS NOT NULL AND expires_at <= DATE_ADD(CURDATE(), INTERVAL :ed DAY)';
            $bind[':ed'] = [$expiryDays, PDO::PARAM_INT];
        }
        if ($stockFilter === 'low') {
            $thr = ($lowStockThreshold !== null && $lowStockThreshold > 0) ? $lowStockThreshold : 5;
            $where[] = 'stock <= :thr';
            $bind[':thr'] = [$thr, PDO::PARAM_INT];
        }
        $whereSql = implode(' AND ', $where);

        $trim = trim($q);
        if ($trim === '') {
            $sql = "SELECT COUNT(*) FROM products WHERE $whereSql";
            $stmt = $this->db->prepare($sql);
            foreach ($bind as $k => $v) { $stmt->bindValue($k, $v[0], $v[1]); }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        }
        if (ctype_digit($trim)) {
            $id = (int)$trim;
            $sql = "SELECT COUNT(*) FROM products WHERE id = :id AND $whereSql";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            foreach ($bind as $k => $v) { $stmt->bindValue($k, $v[0], $v[1]); }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        }
        $like = "%$trim%";
        $sql = "SELECT COUNT(*) FROM products WHERE (name LIKE :like OR sku LIKE :like2) AND $whereSql";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':like2', $like, PDO::PARAM_STR);
        foreach ($bind as $k => $v) { $stmt->bindValue($k, $v[0], $v[1]); }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /** Count active products matching search query (empty = all). */
    public function countSearch(string $q = '', ?int $categoryId = null): int {
        if ($q === '') {
            if ($categoryId !== null) {
                $st = $this->db->prepare("SELECT COUNT(*) FROM products WHERE status = 'active' AND category_id = :cid");
                $st->bindValue(':cid', $categoryId, PDO::PARAM_INT);
                $st->execute();
                return (int)$st->fetchColumn();
            }
            return (int)$this->db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
        }
        $trim = trim($q);
        if (ctype_digit($trim)) {
            $id = (int)$trim;
            $sql = "SELECT COUNT(*) FROM products WHERE id = :id AND status = 'active'" . ($categoryId !== null ? " AND category_id = :cid" : "");
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if ($categoryId !== null) { $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT); }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        }
        $like = "%$trim%";
        $sql = "SELECT COUNT(*) FROM products WHERE status = 'active' AND (name LIKE :like OR sku LIKE :like2)" . ($categoryId !== null ? " AND category_id = :cid" : "");
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':like2', $like, PDO::PARAM_STR);
        if ($categoryId !== null) { $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT); }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /** Paginated active product search. */
    public function searchPaginated(string $q, int $limit, int $offset, ?int $categoryId = null): array {
        if ($limit < 9) { $limit = 9; }
        if ($offset < 0) { $offset = 0; }
        if ($q === '') {
            if ($categoryId !== null) {
                $stmt = $this->db->prepare("SELECT * FROM products WHERE status = 'active' AND category_id = :cid ORDER BY display_no ASC, id ASC LIMIT :lim OFFSET :off");
                $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT);
                $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            }
            $stmt = $this->db->prepare("SELECT * FROM products WHERE status = 'active' ORDER BY display_no ASC, id ASC LIMIT :lim OFFSET :off");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $trim = trim($q);
        if (ctype_digit($trim)) {
            $id = (int)$trim;
            $sql = "SELECT * FROM products WHERE id = :id AND status = 'active'" . ($categoryId !== null ? " AND category_id = :cid" : "") . " ORDER BY id ASC LIMIT :lim OFFSET :off";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if ($categoryId !== null) { $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT); }
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like = "%$trim%";
        // Use only named parameters to avoid mixing positional and named placeholders
        $sql = "SELECT * FROM products WHERE status = 'active' AND (name LIKE :like OR sku LIKE :like2)" . ($categoryId !== null ? " AND category_id = :cid" : "") . " ORDER BY display_no ASC, id ASC LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':like2', $like, PDO::PARAM_STR);
        if ($categoryId !== null) { $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT); }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $d): int {
        // Assign next display_no sequentially (compact numbering)
        $next = (int)$this->db->query('SELECT COALESCE(MAX(display_no),0)+1 FROM products')->fetchColumn();
        $sql = 'INSERT INTO products (display_no, sku, name, description, image, stock, price, expires_at, status, category_id, supplier_id, shelf, `row`, `position`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $next,
            $d['sku'],
            $d['name'],
            $d['description'],
            $d['image'] ?? null,
            $d['stock'],
            $d['price'],
            $d['expires_at'],
            $d['status'],
            $d['category_id'] ?? null,
            $d['supplier_id'] ?? null,
            $d['shelf'] ?? null,
            $d['row'] ?? null,
            $d['position'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $sql = 'UPDATE products SET sku=?, name=?, description=?, image=?, stock=?, price=?, expires_at=?, status=?, category_id=?, supplier_id=?, shelf=?, `row`=?, `position`=? WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $d['sku'],
            $d['name'],
            $d['description'],
            $d['image'] ?? null,
            $d['stock'],
            $d['price'],
            $d['expires_at'],
            $d['status'],
            $d['category_id'] ?? null,
            $d['supplier_id'] ?? null,
            $d['shelf'] ?? null,
            $d['row'] ?? null,
            $d['position'] ?? null,
            $id
        ]);
    }

    public function delete(int $id): bool {
        // Do not hard-delete if there are sale_items referencing this product
        $chk = $this->db->prepare('SELECT COUNT(*) FROM sale_items WHERE product_id = ?');
        $chk->execute([$id]);
        if ((int)$chk->fetchColumn() > 0) {
            return false; // blocked by FK usage
        }
        // Compact numbering by shifting display_no down for items after the deleted one
        $this->db->beginTransaction();
        try {
            // Get display_no of the product
            $sel = $this->db->prepare('SELECT display_no FROM products WHERE id = ? FOR UPDATE');
            $sel->execute([$id]);
            $dn = $sel->fetchColumn();
            if ($dn === false) { $this->db->rollBack(); return false; }
            // Delete
            $del = $this->db->prepare('DELETE FROM products WHERE id = ?');
            $del->execute([$id]);
            // Shift others
            $upd = $this->db->prepare('UPDATE products SET display_no = display_no - 1 WHERE display_no > ?');
            $upd->execute([(int)$dn]);
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function countExpired(): int {
        return (int)$this->db->query("SELECT COUNT(*) c FROM products WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at <= CURDATE()")
            ->fetchColumn();
    }

    public function deleteExpired(): int {
        // Solo eliminar productos vencidos que NO están referenciados en sale_items (FK ON DELETE RESTRICT)
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM products p
                 WHERE p.expires_at IS NOT NULL
                   AND p.expires_at <= CURDATE()
                   AND p.status = 'active'
                   AND NOT EXISTS (SELECT 1 FROM sale_items si WHERE si.product_id = p.id)"
            );
            $stmt->execute();
            $deleted = $stmt->rowCount();
            if ($deleted > 0) {
                // Resequence compact numbering after bulk deletions
                $this->db->query('SET @i := 0');
                $this->db->query('UPDATE products SET display_no = (@i := @i + 1) ORDER BY id ASC');
            }
            $this->db->commit();
            return $deleted;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function listExpired(): array {
        $sql = "SELECT * FROM products WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at <= CURDATE() ORDER BY expires_at ASC, name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function listExpiringWithin(int $days): array {
        if ($days < 0) { $days = 0; }
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' 
                AND p.expires_at IS NOT NULL 
                AND p.expires_at >= CURDATE() 
                AND p.expires_at <= DATE_ADD(CURDATE(), INTERVAL :d DAY) 
                ORDER BY p.expires_at ASC, p.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':d', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function retireExpired(): int {
        $stmt = $this->db->prepare("UPDATE products SET status = 'retired', stock = 0 WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at <= CURDATE()");
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function listRetired(): array {
        $sql = "SELECT * FROM products WHERE status = 'retired' ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function reactivate(int $id): bool {
        $stmt = $this->db->prepare("UPDATE products SET status = 'active' WHERE id = ? AND status = 'retired'");
        return $stmt->execute([$id]);
    }

    /** Deactivate a product (soft) by marking it as retired and zeroing stock. */
    public function retire(int $id): bool {
        $stmt = $this->db->prepare("UPDATE products SET status = 'retired', stock = 0 WHERE id = ? AND status = 'active'");
        return $stmt->execute([$id]);
    }

    /** Given product IDs, return an array of IDs that are referenced in sales or sale_items. */
    public function idsWithSales(array $ids): array {
        $ids = array_values(array_unique(array_map('intval', array_filter($ids, fn($x) => is_numeric($x)))));
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        // Check both historical schemas: direct sales.product_id and normalized sale_items.product_id
        $sql = "
            SELECT DISTINCT product_id FROM sale_items WHERE product_id IN ($placeholders)
            UNION
            SELECT DISTINCT product_id FROM sales WHERE product_id IN ($placeholders)
        ";
        $stmt = $this->db->prepare($sql);
        // bind ids twice (for both IN lists)
        $bind = array_merge($ids, $ids);
        $stmt->execute($bind);
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $r) { $out[(int)($r['product_id'] ?? 0)] = true; }
        return array_keys($out);
    }

    /** Find an ACTIVE product by exact SKU. Returns row or null. */
    public function findBySkuActive(string $sku): ?array {
        $trim = trim($sku);
        if ($trim === '') return null;
        $stmt = $this->db->prepare("SELECT * FROM products WHERE status = 'active' AND sku = ? LIMIT 1");
        $stmt->execute([$trim]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
