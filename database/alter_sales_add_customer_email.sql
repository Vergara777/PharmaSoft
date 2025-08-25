-- Add customer_email column to sales table
ALTER TABLE sales
  ADD COLUMN customer_email VARCHAR(191) NULL AFTER customer_phone;

-- Optional helpful index if searching by email later
-- CREATE INDEX idx_sales_customer_email ON sales (customer_email);
