USE pharmasoft;

-- Add sequential display number for products (compact numbering 1..N)
ALTER TABLE products
  ADD COLUMN display_no INT NULL AFTER id;

-- Backfill sequential numbers in a single pass
SET @i := 0;
UPDATE products SET display_no = (@i := @i + 1) ORDER BY id ASC;

-- Ensure not null after backfill
ALTER TABLE products
  MODIFY COLUMN display_no INT NOT NULL;

-- Optional index for ordering
CREATE INDEX idx_products_display_no ON products(display_no);
