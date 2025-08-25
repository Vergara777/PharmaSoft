<?php
// TEMPORAL: Generador de hash para contraseña
// Uso: http://localhost/PharmaSoft2.0/public/gen_hash.php?pwd=TuContraseña
// Luego copia el hash y actualiza en phpMyAdmin: UPDATE users SET password_hash='AQUI_HASH' WHERE email='admin@pharmasoft.local';

header('Content-Type: text/plain; charset=utf-8');
$pwd = isset($_GET['pwd']) ? (string)$_GET['pwd'] : 'Admin123!';
echo password_hash($pwd, PASSWORD_DEFAULT), "\n";
