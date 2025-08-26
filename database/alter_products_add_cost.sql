USE pharmasoft;

-- Add purchase cost column to products (COP)
ALTER TABLE products ADD COLUMN IF NOT EXISTS cost DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER price;

-- Optional: initialize from existing data source if available
-- UPDATE products SET cost = 0 WHERE cost IS NULL;
