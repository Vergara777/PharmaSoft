<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Supplier extends Model {
    public function all(): array {
        return $this->db->query("SELECT id, name, phone, email FROM suppliers ORDER BY name ASC")->fetchAll();
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM suppliers WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
    public function create(array $d): int {
        $st = $this->db->prepare('INSERT INTO suppliers (name, phone, email, created_at) VALUES (?, ?, ?, NOW())');
        $st->execute([
            trim($d['name'] ?? ''),
            trim($d['phone'] ?? ''),
            trim($d['email'] ?? ''),
        ]);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id, array $d): void {
        $st = $this->db->prepare('UPDATE suppliers SET name = ?, phone = ?, email = ? WHERE id = ?');
        $st->execute([
            trim($d['name'] ?? ''),
            trim($d['phone'] ?? ''),
            trim($d['email'] ?? ''),
            $id
        ]);
    }
    public function delete(int $id): void {
        $st = $this->db->prepare('DELETE FROM suppliers WHERE id = ?');
        $st->execute([$id]);
    }
}
