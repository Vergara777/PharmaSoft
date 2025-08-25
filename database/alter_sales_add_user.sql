USE pharmasoft;

-- Add user attribution columns to sales
ALTER TABLE sales
  ADD COLUMN user_id INT NULL AFTER customer_phone,
  ADD COLUMN user_role VARCHAR(32) NULL AFTER user_id,
  ADD COLUMN user_name VARCHAR(150) NULL AFTER user_role,
  ADD INDEX idx_sales_user_id (user_id);
