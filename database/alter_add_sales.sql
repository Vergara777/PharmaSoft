USE pharmasoft;

CREATE TABLE IF NOT EXISTS sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  qty INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  customer_name VARCHAR(150) NULL,
  customer_phone VARCHAR(30) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sales_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX (created_at)
) ENGINE=InnoDB;

-- Extend sales with customer info
ALTER TABLE sales
  ADD COLUMN customer_name VARCHAR(150) NULL AFTER total,
  ADD COLUMN customer_phone VARCHAR(30) NULL AFTER customer_name;
