# XelaExpress

Sistema de Punto de Venta minimalista y llamativo para consumo diario.

## Estructura del Proyecto

- `assets/`: Archivos estáticos (CSS, JS, imágenes)
- `config/`: Configuración y conexión a la base de datos
- `db/`: Scripts de base de datos
- `modules/`: Módulos principales (productos, ventas, usuarios)
- `templates/`: Plantillas reutilizables (header, footer, navbar)
- `index.php`: Página principal
- `login.php`: Login de usuarios
- `logout.php`: Cierre de sesión
- `dashboard.php`: Panel principal tras login

## Requisitos
- PHP 7.x o superior
- MySQL/MariaDB (XAMPP recomendado)

## Instalación
1. Clona o copia este proyecto en tu carpeta `htdocs` de XAMPP.
2. Importa el archivo `db/xelaexpress.sql` en tu base de datos MySQL/MariaDB.
3. Configura los datos de conexión en `config/db.php`.
4. Accede a `http://localhost/XelaExpress` en tu navegador. 