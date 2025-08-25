<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Product extends Model {
    public function all(): array {
        // Include stock so sales create view can validate availability correctly
        return $this->db->query("SELECT id, sku, name, image, stock, price, expires_at FROM products WHERE status = 'active' ORDER BY name ASC")->fetchAll();
    }
    public function search(string $q = ''): array {
        if ($q === '') {
            $stmt = $this->db->query("SELECT * FROM products WHERE status = 'active' ORDER BY display_no ASC, id ASC");
            return $stmt->fetchAll();
        }
        $trim = trim($q);
        // If the query is a positive integer, prioritize exact ID match first
        if (ctype_digit($trim)) {
            $id = (int)$trim;
            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id AND status = 'active' ORDER BY id ASC");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        // Default: search by name or SKU
        $like = "%$trim%";
        $stmt = $this->db->prepare("SELECT * FROM products WHERE status = 'active' AND (name LIKE ? OR sku LIKE ?) ORDER BY display_no ASC, id ASC");
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    /** Count active products matching search query (empty = all). */
    public function countSearch(string $q = ''): int {
        if ($q === '') {
            return (int)$this->db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
        }
        $trim = trim($q);
        if (ctype_digit($trim)) {
            $id = (int)$trim;
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE id = :id AND status = 'active'");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        }
        $like = "%$trim%";
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE status = 'active' AND (name LIKE ? OR sku LIKE ?)");
        $stmt->execute([$like, $like]);
        return (int)$stmt->fetchColumn();
    }

    /** Paginated active product search. */
    public function searchPaginated(string $q, int $limit, int $offset): array {
        if ($limit < 9) { $limit = 9; }
        if ($offset < 0) { $offset = 0; }
        if ($q === '') {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE status = 'active' ORDER BY display_no ASC, id ASC LIMIT :lim OFFSET :off");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $trim = trim($q);
        if (ctype_digit($trim)) {
            $id = (int)$trim;
            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id AND status = 'active' ORDER BY id ASC LIMIT :lim OFFSET :off");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $like = "%$trim%";
        // Use only named parameters to avoid mixing positional and named placeholders
        $stmt = $this->db->prepare("SELECT * FROM products WHERE status = 'active' AND (name LIKE :like OR sku LIKE :like2) ORDER BY display_no ASC, id ASC LIMIT :lim OFFSET :off");
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->bindValue(':like2', $like, PDO::PARAM_STR);
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
        $sql = 'INSERT INTO products (display_no, sku, name, description, image, stock, price, expires_at, status) VALUES (?,?,?,?,?,?,?,?,?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$next,$d['sku'],$d['name'],$d['description'],$d['image'] ?? null,$d['stock'],$d['price'],$d['expires_at'],$d['status']]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $sql = 'UPDATE products SET sku=?, name=?, description=?, image=?, stock=?, price=?, expires_at=?, status=? WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$d['sku'],$d['name'],$d['description'],$d['image'] ?? null,$d['stock'],$d['price'],$d['expires_at'],$d['status'],$id]);
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
        return (int)$this->db->query("SELECT COUNT(*) c FROM products WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at < CURDATE()")
            ->fetchColumn();
    }

    public function deleteExpired(): int {
        // Solo eliminar productos vencidos que NO están referenciados en sale_items (FK ON DELETE RESTRICT)
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM products p
                 WHERE p.expires_at IS NOT NULL
                   AND p.expires_at < CURDATE()
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
        $sql = "SELECT * FROM products WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at < CURDATE() ORDER BY expires_at ASC, name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function listExpiringWithin(int $days): array {
        if ($days < 0) { $days = 0; }
        $stmt = $this->db->prepare("SELECT * FROM products WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at >= CURDATE() AND expires_at <= DATE_ADD(CURDATE(), INTERVAL :d DAY) ORDER BY expires_at ASC, name ASC");
        $stmt->bindValue(':d', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function retireExpired(): int {
        $stmt = $this->db->prepare("UPDATE products SET status = 'retired', stock = 0 WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at < CURDATE()");
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
}



