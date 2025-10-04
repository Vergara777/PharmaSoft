-- Crear tabla para el registro de inicios de sesión
CREATE TABLE IF NOT EXISTS user_login_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  role VARCHAR(50) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  user_agent TEXT NULL,
  login_time DATETIME NOT NULL,
  status ENUM('success', 'failed') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear índices para mejorar el rendimiento
CREATE INDEX idx_user_login_logs_user_id ON user_login_logs(user_id);
CREATE INDEX idx_user_login_logs_login_time ON user_login_logs(login_time);
CREATE INDEX idx_user_login_logs_status ON user_login_logs(status);
CREATE INDEX idx_user_login_logs_ip ON user_login_logs(ip_address);