USE pharmasoft;

-- Create sale_items table for multi-item carts
CREATE TABLE IF NOT EXISTS sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_id INT NOT NULL,
  product_id INT NOT NULL,
  qty INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_sale_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX (sale_id),
  INDEX (product_id)
) ENGINE=InnoDB;

-- Ensure sales table has a total column (if not already present)
-- MySQL 8+: IF NOT EXISTS is supported; if on MySQL 5.7, run manually without IF NOT EXISTS
ALTER TABLE sales ADD COLUMN IF NOT EXISTS total DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER unit_price;

-- Allow NULLs for legacy single-item fields so we can create a sale header without linking a single product
ALTER TABLE sales
  MODIFY COLUMN product_id INT NULL,
  MODIFY COLUMN qty INT NULL,
  MODIFY COLUMN unit_price DECIMAL(10,2) NULL;

-- Optional: keep legacy columns (product_id, qty, unit_price) for backward compatibility,
-- but new code will insert into sale_items and compute sales.total as SUM(line_total).
