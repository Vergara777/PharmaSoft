USE pharmasoft;

ALTER TABLE products
  ADD COLUMN status ENUM('active','retired') NOT NULL DEFAULT 'active' AFTER expires_at,
  ADD INDEX idx_products_status (status);
