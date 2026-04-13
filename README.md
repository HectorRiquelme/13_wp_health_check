# WP Health Check

Plugin de WordPress que diagnostica problemas comunes automáticamente y ofrece correcciones seguras.

## Instalación

1. Copiar la carpeta `wp-health-check/` dentro de `wp-content/plugins/`.
2. Activar el plugin desde **Plugins** en el panel de WordPress.
3. Ir a **Herramientas > Health Check** para ejecutar el diagnóstico.

## Funcionalidades

- **6 checks independientes**: jQuery duplicado, scripts bloqueantes, imágenes sin alt text o sobredimensionadas, viewport responsive, seguridad (HTTPS, debug, .htaccess) y actualizaciones (PHP, WordPress).
- **Semáforo visual**: cada check muestra verde (correcto), amarillo (advertencia) o rojo (error).
- **Fix automático**: agrega alt text faltante, mueve scripts al footer y aplica `defer`.
- **AJAX completo**: diagnóstico sin recargar página con barra de progreso.
- **Historial**: guarda los últimos 50 diagnósticos con fecha en `wp_options`.
- **Export JSON**: descarga el último reporte como archivo `.json`.

## Estructura

| Archivo | Descripción |
|---|---|
| `wp-health-check.php` | Archivo principal del plugin |
| `includes/class-health-check-runner.php` | Orquesta la ejecución de checks |
| `includes/class-health-check-admin.php` | Página admin y endpoints AJAX |
| `includes/class-health-check-fixer.php` | Correcciones automáticas |
| `includes/checks/class-check-*.php` | Checks individuales |
| `admin/views/admin-page.php` | Vista HTML del panel |
| `admin/css/admin-style.css` | Estilos del panel |
| `admin/js/admin-script.js` | Lógica JS con jQuery |

## Stack

PHP, WordPress API, JavaScript, jQuery, CSS.

## Autor

Hector Riquelme - [GitHub](https://github.com/HectorRiquelme)
