-- Add optional product image filename column
-- Run this once in your MySQL/MariaDB database connected to PharmaSoft
-- Example: mysql -u user -p pharmasoft < alter_products_add_image.sql

ALTER TABLE products
  ADD COLUMN image VARCHAR(255) NULL AFTER description;

-- If you want a quick backfill, leave NULLs as-is; upload via UI to populate.
