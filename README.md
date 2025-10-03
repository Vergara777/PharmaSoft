# PharmaSoft 2.0

![PharmaSoft Logo](https://via.placeholder.com/150x50?text=PharmaSoft)  
Sistema integral de gestión farmacéutica desarrollado con arquitectura MVC en PHP.

## 🚀 Características Principales

### Gestión de Inventario
- Control completo de productos y existencias
- Gestión de categorías y ubicaciones
- Alertas de stock mínimo y caducidad
- Códigos de barra personalizados

### Ventas y Facturación
- Punto de venta (POS) integrado
- Gestión de clientes
- Facturación electrónica
- Historial de transacciones

### Usuarios y Permisos
- Múltiples roles de usuario
- Permisos granulares
- Registro de actividades
- Autenticación segura

### Reportes y Análisis
- Ventas por período
- Productos más vendidos
- Inventario actual
- Métricas de rendimiento

## 📋 Requisitos Técnicos

### Servidor
- PHP 7.4 o superior
- MySQL 5.7/8.0 o MariaDB 10.3+
- Servidor web Apache 2.4+ o Nginx
- Extensión PDO habilitada
- `mod_rewrite` activado
- Extensión GD para generación de códigos de barras

### Cliente
- Navegador web moderno (Chrome, Firefox, Edge, Safari)
- JavaScript habilitado
- Resolución mínima recomendada: 1366x768

## 🛠️ Instalación

### 1. Requisitos Previos
```bash
# Clonar repositorio
git clone https://github.com/tuusuario/PharmaSoft2.0.git
cd PharmaSoft2.0
```

### 2. Configuración de Base de Datos
1. Crear una base de datos MySQL
2. Importar la estructura inicial:
   ```sql
   mysql -u usuario -p nombre_base_datos < database/schema.sql
   ```
3. Opcional: Importar datos de ejemplo:
   ```sql
   mysql -u usuario -p nombre_base_datos < database/seed.sql
   ```

### 3. Configuración de la Aplicación
1. Copiar el archivo de configuración de ejemplo:
   ```bash
   cp app/config/config.example.php app/config/config.php
   ```
2. Editar `app/config/config.php` con tus credenciales de base de datos

### 4. Permisos de Directorios
Asegúrate de que los siguientes directorios tengan permisos de escritura:
- `public/uploads/`
- `app/storage/logs/`

### 5. Acceso Inicial
- URL: `http://tudominio.com/PharmaSoft2.0/public`
- Usuario: `admin@pharmasoft.local`
- Contraseña: `Admin123!`

## 🏗️ Estructura del Proyecto

```
PharmaSoft2.0/
├── app/                   # Código fuente de la aplicación
│   ├── config/           # Archivos de configuración
│   ├── controllers/      # Controladores
│   ├── core/             # Núcleo MVC
│   ├── helpers/          # Clases de ayuda
│   ├── models/           # Modelos de datos
│   ├── views/            # Vistas y plantillas
│   └── storage/          # Almacenamiento (logs, caché)
├── database/             # Migraciones y semillas
├── public/               # Punto de entrada público
│   ├── css/              # Hojas de estilo
│   ├── js/               # Scripts JavaScript
│   ├── img/              # Imágenes
│   └── index.php         # Front controller
└── vendor/               # Dependencias (composer)
```

## 🔒 Seguridad

### Medidas de Seguridad Implementadas
- Autenticación segura con hash bcrypt
- Protección CSRF en todos los formularios
- Sanitización de entradas de usuario
- Escape de salidas HTML
- Headers de seguridad HTTP
- Protección contra inyección SQL con consultas preparadas
- Límite de intentos de inicio de sesión

### Buenas Prácticas
- No almacenar contraseñas en texto plano
- Validación de datos en servidor y cliente
- Registro de actividades sensibles
- Actualizaciones de seguridad periódicas

## 📱 Interfaz de Usuario

### Características de la Interfaz
- Diseño responsivo (móvil, tableta, escritorio)
- Tema claro/oscuro
- Panel de control personalizable
- Gráficos y reportes interactivos
- Búsqueda avanzada con filtros

## 📈 Reportes Disponibles

### Ventas
- Ventas por período
- Productos más vendidos
- Ventas por vendedor
- Historial de transacciones

### Inventario
- Niveles de stock
- Productos próximos a agotarse
- Productos próximos a vencer
- Movimientos de inventario

### Clientes
- Historial de compras
- Puntos de fidelidad
- Cumpleaños
- Compras recurrentes

## 🔄 Mantenimiento

### Actualizaciones
```bash
# Actualizar dependencias
composer update

# Aplicar migraciones
php database/migrations/run.php
```

### Copias de Seguridad
Se recomienda configurar copias de seguridad automáticas de:
- Base de datos
- Archivos subidos
- Archivos de configuración

## 🤝 Contribuir

1. Haz un Fork del proyecto
2. Crea una rama para tu característica (`git checkout -b feature/nueva-funcionalidad`)
3. Haz commit de tus cambios (`git commit -am 'Añadir nueva funcionalidad'`)
4. Haz push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia
Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## ✉️ Contacto
- **Soporte**: soporte@pharmasoft.com
- **Sitio Web**: [www.pharmasoft.com](https://www.pharmasoft.com)
- **Teléfono**: +1 234 567 890

---

<div align="center">
  <p>Hecho con ❤️ por el equipo de PharmaSoft</p>
  <p>© 2023 PharmaSoft - Todos los derechos reservados</p>
</div>
