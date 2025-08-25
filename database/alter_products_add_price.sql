USE pharmasoft;

-- Add price column to products
ALTER TABLE products ADD COLUMN IF NOT EXISTS price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER description;

-- Optional: initialize price from existing data if you have any external source
-- UPDATE products SET price = 0 WHERE price IS NULL;
