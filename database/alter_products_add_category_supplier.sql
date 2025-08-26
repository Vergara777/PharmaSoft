-- Agregar columnas de categoría y proveedor a products
ALTER TABLE products
  ADD COLUMN category_id INT NULL AFTER status,
  ADD COLUMN supplier_id INT NULL AFTER category_id;

-- Crear llaves foráneas si no existen las relaciones aún
ALTER TABLE products
  ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL ON UPDATE CASCADE;
