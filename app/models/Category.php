<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Category extends Model {
    public function all(): array {
        return $this->db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
    public function create(array $d): int {
        $st = $this->db->prepare('INSERT INTO categories (name, created_at) VALUES (?, NOW())');
        $st->execute([trim($d['name'] ?? '')]);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id, array $d): void {
        $st = $this->db->prepare('UPDATE categories SET name = ? WHERE id = ?');
        $st->execute([trim($d['name'] ?? ''), $id]);
    }
    public function delete(int $id): void {
        $st = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        $st->execute([$id]);
    }
}
