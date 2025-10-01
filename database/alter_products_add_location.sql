-- Añadir columnas de ubicación a la tabla de productos
ALTER TABLE products
ADD COLUMN shelf VARCHAR(10) NULL COMMENT 'Estante donde se ubica el producto' AFTER stock,
ADD COLUMN `row` VARCHAR(10) NULL COMMENT 'Fila dentro del estante' AFTER shelf,
ADD COLUMN `position` VARCHAR(10) NULL COMMENT 'Posición dentro de la fila' AFTER `row`,
ADD INDEX `idx_location` (`shelf`, `row`, `position`);

-- Actualizar la vista de productos si existe
-- (opcional, solo si existe una vista que necesite ser actualizada)
-- DROP VIEW IF EXISTS vw_products;
-- CREATE VIEW vw_products AS SELECT * FROM products;

-- Comentario: Esta migración agrega las columnas necesarias para el seguimiento de ubicación
-- de productos en el almacén, incluyendo estante, fila y posición.
