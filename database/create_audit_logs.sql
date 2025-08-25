-- audit_logs table to track changes
CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_id INT NULL,
  user_name VARCHAR(255) NULL,
  entity VARCHAR(64) NOT NULL,
  entity_id INT NULL,
  action VARCHAR(32) NOT NULL,
  changes_json TEXT NULL,
  ip VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  KEY idx_audit_entity (entity, entity_id),
  KEY idx_audit_user (user_id),
  KEY idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
