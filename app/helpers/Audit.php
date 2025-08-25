<?php
namespace App\Helpers;

use App\Config\Database;
use App\Helpers\Auth;

class Audit {
    /**
     * Log an audit entry.
     * @param string $entity e.g. 'product', 'user'
     * @param int|null $entityId
     * @param string $action e.g. 'create','update','update_profile'
     * @param array|null $changes associative array of changes; will be JSON encoded
     */
    public static function log(string $entity, ?int $entityId, string $action, ?array $changes = null): void {
        try {
            $db = Database::getConnection();
            $uid = Auth::id();
            $uname = Auth::user()['name'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $json = $changes ? json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, user_name, entity, entity_id, action, changes_json, ip, user_agent) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$uid, $uname, $entity, $entityId, $action, $json, $ip, $ua]);
        } catch (\Throwable $e) {
            // Fail silently to not break the main flow
        }
    }

    /**
     * Build a diff [field => ['from'=>x,'to'=>y]] only for changed values.
     * Optionally restrict to specific keys.
     */
    public static function diff(array $before, array $after, ?array $onlyKeys = null): array {
        $diff = [];
        $keys = $onlyKeys ?: array_unique(array_merge(array_keys($before), array_keys($after)));
        foreach ($keys as $k) {
            $b = $before[$k] ?? null;
            $a = $after[$k] ?? null;
            if ($b !== $a) {
                $diff[$k] = ['from' => $b, 'to' => $a];
            }
        }
        return $diff;
    }
}
