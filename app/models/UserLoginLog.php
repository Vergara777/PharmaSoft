<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class UserLoginLog extends Model {
    /**
     * Log a user login attempt
     */
    public function logLogin(int $userId, string $name, string $role, string $ip, string $userAgent, string $status): int {
        $sql = "INSERT INTO user_login_logs (user_id, name, role, ip_address, user_agent, login_time, status) 
                VALUES (:user_id, :name, :role, :ip_address, :user_agent, :login_time, :status)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => $name,
            ':role' => $role,
            ':ip_address' => $ip,
            ':user_agent' => $userAgent,
            ':login_time' => date('Y-m-d H:i:s'),
            ':status' => $status
        ]);
        
        // Return the ID of the inserted record
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Update logout time for a login record
     */
    public function logLogout(int $loginId): bool {
        error_log("Intentando registrar cierre de sesión para login_id: " . $loginId);
        
        try {
            $sql = "UPDATE user_login_logs 
                    SET logout_time = :logout_time 
                    WHERE id = :id AND status = 'success' AND logout_time IS NULL";
                    
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':id' => $loginId,
                ':logout_time' => date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                $count = $stmt->rowCount();
                if ($count > 0) {
                    error_log("Cierre de sesión registrado correctamente para login_id: " . $loginId);
                } else {
                    error_log("Advertencia: No se actualizó ningún registro para login_id: " . $loginId . ". ¿Ya existe un logout_time?");
                }
                return $count > 0;
            } else {
                $error = $stmt->errorInfo();
                error_log("Error en la consulta SQL al registrar cierre de sesión: " . print_r($error, true));
                return false;
            }
        } catch (\Exception $e) {
            error_log("Excepción al registrar cierre de sesión: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the last successful login ID for a user
     */
    public function getLastLoginId(int $userId): ?int {
        $sql = "SELECT id FROM user_login_logs 
                WHERE user_id = :user_id AND status = 'success' 
                ORDER BY login_time DESC LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['id'] : null;
    }

    /**
     * Get login logs with optional filters
     */
    public function getLogs(int $limit = 50, ?int $userId = null, ?string $dateFrom = null, ?string $dateTo = null): array {
        $sql = "SELECT * FROM user_login_logs WHERE 1=1";
        
        $params = [];
        
        if ($userId !== null) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        if ($dateFrom !== null) {
            $sql .= " AND login_time >= :date_from";
            $params[':date_from'] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo !== null) {
            $sql .= " AND login_time <= :date_to";
            $params[':date_to'] = $dateTo . ' 23:59:59';
        }
        
        $sql .= " ORDER BY login_time DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get failed login attempts count for an IP in the last hour
     */
    public function getRecentFailedAttempts(string $ip, int $minutes = 60): int {
        $sql = "SELECT COUNT(*) as count 
                FROM user_login_logs 
                WHERE ip_address = :ip 
                AND status = 'failed' 
                AND login_time >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ip' => $ip,
            ':minutes' => $minutes
        ]);
        
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get all login logs with pagination
     */
    public function getAllLogs(int $page = 1, int $perPage = 20): array {
        // Count total records
        $countStmt = $this->db->query("SELECT COUNT(*) as total FROM user_login_logs");
        $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        // Get paginated logs
        $sql = "SELECT * FROM user_login_logs 
                ORDER BY login_time DESC 
                LIMIT :offset, :limit";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages
        ];
    }
}
