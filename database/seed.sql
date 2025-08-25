USE pharmasoft;

INSERT INTO users (name,email,role,password_hash) VALUES
('Administrador','admin@pharmasoft.local','admin', '$2y$10$9f7nJ9k9D0jOeVxC4QxF0e8yqj7qD1Z7G2nDk0s1J6v7w2Gk6zv7W');
-- La contraseña real del hash anterior es: Admin123!

INSERT INTO products (sku,name,description,stock,expires_at) VALUES
('AMOX500','Amoxicilina 500mg','Antibiótico de amplio espectro',120, DATE_ADD(CURDATE(), INTERVAL 180 DAY)),
('PARA1G','Paracetamol 1g','Analgésico y antipirético',300, DATE_ADD(CURDATE(), INTERVAL 365 DAY));
