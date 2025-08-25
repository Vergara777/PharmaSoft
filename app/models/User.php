<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model {
    public function findByEmail(string $email): ?array {
        $sql = 'SELECT id, name, email, role, password_hash, avatar FROM users WHERE email = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function all(): array {
        return $this->db->query('SELECT id, name, email, role, avatar, created_at FROM users ORDER BY id DESC')->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare('SELECT id, name, email, role, avatar, phone, address, position, hire_date, birth_date, id_number FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, role, password_hash) VALUES (?,?,?,?)');
        $stmt->execute([$data['name'], $data['email'], $data['role'], password_hash($data['password'], PASSWORD_DEFAULT)]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = ['name = ?', 'email = ?', 'role = ?'];
        $params = [$data['name'], $data['email'], $data['role']];
        if (!empty($data['password'])) { $fields[] = 'password_hash = ?'; $params[] = password_hash($data['password'], PASSWORD_DEFAULT); }
        $params[] = $id;
        $stmt = $this->db->prepare('UPDATE users SET ' . implode(',', $fields) . ' WHERE id = ?');
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function updateAvatar(int $id, string $path): bool {
        $stmt = $this->db->prepare('UPDATE users SET avatar = ? WHERE id = ?');
        return $stmt->execute([$path, $id]);
    }

    public function updateProfile(int $id, array $d): bool {
        $sql = 'UPDATE users SET name = ?, email = ?, phone = ?, address = ?, position = ?, hire_date = ?, birth_date = ?, id_number = ? WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $d['name'],
            $d['email'],
            $d['phone'] ?? null,
            $d['address'] ?? null,
            $d['position'] ?? null,
            $d['hire_date'] ?? null,
            $d['birth_date'] ?? null,
            $d['id_number'] ?? null,
            $id
        ]);
    }
}
