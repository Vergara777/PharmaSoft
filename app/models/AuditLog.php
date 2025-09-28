<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class AuditLog extends Model {
    public function countFiltered(?string $from, ?string $to, array $filters = []): int {
        [$where, $params] = $this->buildWhere($from, $to, $filters);
        $sql = 'SELECT COUNT(*) FROM `audit_logs` ' . ($where ? ('WHERE ' . $where) : '');
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function listPaginated(int $limit, int $offset, ?string $from, ?string $to, array $filters = []): array {
        if ($limit < 10) { $limit = 10; }
        if ($offset < 0) { $offset = 0; }
        [$where, $params] = $this->buildWhere($from, $to, $filters);
        $sql = 'SELECT `id`, `created_at`, `user_id`, `user_name`, `entity`, `entity_id`, `action`, `changes_json`, `ip`, `user_agent`
                FROM `audit_logs` ' . ($where ? ('WHERE ' . $where) : '') . ' ORDER BY `id` DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allAsc(?string $from, ?string $to, array $filters = []): array {
        [$where, $params] = $this->buildWhere($from, $to, $filters);
        $sql = 'SELECT `id`, `created_at`, `user_id`, `user_name`, `entity`, `entity_id`, `action`, `changes_json`, `ip`, `user_agent`
                FROM `audit_logs` ' . ($where ? ('WHERE ' . $where) : '') . ' ORDER BY `id` ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildWhere(?string $from, ?string $to, array $filters): array {
        $clauses = [];
        $params = [];
        $isYmd = fn(string $d) => (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
        if ($from && $to && $isYmd($from) && $isYmd($to)) {
            $clauses[] = 'DATE(`created_at`) BETWEEN :from AND :to';
            $params['from'] = $from; $params['to'] = $to;
        }
        // Support filter by primary id and id ranges
        $id = trim((string)($filters['id'] ?? ''));
        if ($id !== '' && ctype_digit($id)) { $clauses[] = '`id` = :id'; $params['id'] = (int)$id; }
        $idFrom = trim((string)($filters['id_from'] ?? ''));
        if ($idFrom !== '' && ctype_digit($idFrom)) { $clauses[] = '`id` >= :id_from'; $params['id_from'] = (int)$idFrom; }
        $idTo = trim((string)($filters['id_to'] ?? ''));
        if ($idTo !== '' && ctype_digit($idTo)) { $clauses[] = '`id` <= :id_to'; $params['id_to'] = (int)$idTo; }
        $entity = trim((string)($filters['entity'] ?? ''));
        if ($entity !== '') { $clauses[] = '`entity` = :entity'; $params['entity'] = $entity; }
        $action = trim((string)($filters['action'] ?? ''));
        if ($action !== '') { $clauses[] = '`action` = :action'; $params['action'] = $action; }
        $uid = trim((string)($filters['user_id'] ?? ''));
        if ($uid !== '' && ctype_digit($uid)) { $clauses[] = '`user_id` = :uid'; $params['uid'] = (int)$uid; }
        $uname = trim((string)($filters['user_name'] ?? ''));
        if ($uname !== '') { $clauses[] = '`user_name` LIKE :uname'; $params['uname'] = '%' . $uname . '%'; }
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $clauses[] = '(`changes_json` LIKE :q1 OR `user_agent` LIKE :q2 OR `ip` LIKE :q3)';
            $params['q1'] = '%' . $q . '%';
            $params['q2'] = '%' . $q . '%';
            $params['q3'] = '%' . $q . '%';
        }
        return [implode(' AND ', $clauses), $params];
    }
}
