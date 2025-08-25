# PharmaSoft 2.0

Plataforma web de gestión de farmacia (PHP MVC + AdminLTE + MySQL).

## Requisitos
- PHP 7.4+ / 8.x
- MySQL 5.7/8.0
- Apache (WAMP/XAMPP)

## Instalación rápida (WAMP/XAMPP)
1. Clona o copia este proyecto en `www/PharmaSoft2.0` (WAMP) o `htdocs/PharmaSoft2.0` (XAMPP).
2. Crea la base de datos y tablas:
   - Importa `database/schema.sql` y luego `database/seed.sql` en MySQL.
3. Configura credenciales en `app/config/config.php`.
4. Apache: asegúrate de tener `mod_rewrite` activo.
5. Accede a `http://localhost/PharmaSoft2.0/public/`.

Credenciales iniciales:
- admin@pharmasoft.local / Admin123!

## Estructura
- `public/` Front controller y activos públicos.
- `app/core/` Núcleo MVC (Router, Controller, View, Model).
- `app/controllers/`, `app/models/`, `app/views/`
- `app/config/` Configuración y conexión DB.
- `app/helpers/` Utilidades (seguridad, auth).
- `database/` Scripts SQL.

## Seguridad
- Hash de contraseñas con `password_hash`/`password_verify`.
- PDO + consultas preparadas.
- CSRF tokens en formularios.
- Sanitización y escape en vistas.

## Roadmap
- Módulos futuros: ventas, proveedores, reportes.
