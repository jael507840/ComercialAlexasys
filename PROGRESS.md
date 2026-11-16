# Estado del proyecto Alex@sys

**Fecha de esta revisión:** 16/11/2026
**Entorno verificado:** PHP 8.2.12, MariaDB 10.4.32, base `dbAlexasys` (20 tablas).
**Alcance:** revisión funcional, dashboard por rol, ubicación pública y formulario de contacto.

> Este documento registra únicamente lo comprobado en el entorno local. Las
> pruebas manuales pendientes se indican como tales; no se declaran resultados
> que no se hayan ejecutado.

## 1. Resumen

El sistema se encuentra operativo en el entorno de desarrollo. Las páginas
públicas y los módulos del panel responden sin errores PHP. En esta revisión se
corrigieron defectos que impedían mostrar el dashboard y ejecutar búsquedas, se
completó el dashboard para los tres roles, se creó la vista faltante de pagos de
servicio y se completó y enlazó la colección de iconos. También se agregó la
imagen de ubicación y un formulario de contacto con validación y almacenamiento.

## 2. Correcciones aplicadas en esta revisión

| # | Problema | Archivo(s) | Estado |
|---|---|---|---|
| 1 | El dashboard recibía la clave `datos` pero `renderizar()` usaba `extract($datos, EXTR_SKIP)`; la variable local impedía que las métricas llegaran a la vista. | `Core/bootstrap.php` | Corregido: parámetros renombrados a `$variables` en `renderizar()` y `parcial()`. |
| 2 | Búsquedas con error `HY093`: se reutilizaba el marcador `:q` varias veces con `ATTR_EMULATE_PREPARES = false`. | `Models/Categoria.php`, `Cliente.php`, `Compra.php`, `EquipoCliente.php`, `Garantia.php`, `OrdenServicio.php`, `Producto.php`, `Proveedor.php`, `Venta.php` | Corregido con marcadores únicos `:q1..:qN`. |
| 3 | Faltaba la vista `pago-servicio-nuevo.view.php`; el módulo de pagos de servicio devolvía HTTP 500. | `Views/pago-servicio-nuevo.view.php` | Creada; formulario GET/POST y validaciones verificadas. |
| 4 | El dashboard solo mostraba un resumen general y no se adaptaba al rol. | `Controllers/index.php`, `Views/dashboard.view.php`, `Models/Reporte.php`, `Models/OrdenServicio.php` | Rehecho por perfil (Gerente, Vendedor, Técnico) con métricas reales. |
| 5 | La carpeta `imagen/iconos` estaba incompleta y los iconos no se usaban en el sitio público ni en el panel. | `imagen/iconos/*`, `Views/publico/inicio.php`, `Views/publico/nosotros.php`, `Views/dashboard.view.php`, `assets/css/estilos.css` | 12 iconos SVG nuevos, `vision.png` enlazado; accesos rápidos y tarjetas con iconos. |
| 6 | Acceso directo a controladores sin bloqueo web. | `Controllers/.htaccess` | Añadido `Require all denied`. |
| 7 | La página no mostraba la ubicación y Contactos no permitía enviar consultas. | `Views/publico/nosotros.php`, `Views/publico/contactos.php`, `Controllers/index.php`, `Models/ConsultaContacto.php` | Ubicación visible; formulario con CSRF, validaciones, guardado en MySQL y notificación por correo si SMTP está configurado. |

## 3. Estado por módulo

| Módulo | Implementación | Verificación en esta revisión |
|---|---|---|
| 1. Sitio público (Inicio, Nosotros, Productos, Servicios, Contactos) | Completo | HTTP 200; ubicación cargada; validación del formulario comprobada; envío por correo pendiente de configurar SMTP |
| 2. Acceso y sesiones | Completo | Login correcto para los tres roles; permisos por módulo redirigen al dashboard |
| 3. Dashboard | Completo | HTTP 200 para Gerente, Vendedor y Técnico; 0 avisos PHP; métricas y accesos coherentes con el rol |
| 4. Clientes | Completo | Listado y búsqueda HTTP 200 |
| 5. Categorías | Completo | Listado HTTP 200 |
| 6. Productos | Completo | Listado y búsqueda HTTP 200 |
| 7. Proveedores | Completo | Listado HTTP 200 (solo Gerente) |
| 8. Compras | Completo | Listado HTTP 200 (solo Gerente) |
| 9. Inventario | Completo | Listado HTTP 200; verificación de stock: 12 productos, 0 discrepancias |
| 10. Ventas | Completo | Listado y búsqueda HTTP 200 |
| 11. Pagos de ventas | Completo | Listado HTTP 200; saldos verificados por SQL |
| 12. Equipos de clientes | Completo | Listado HTTP 200 |
| 13. Órdenes de servicio | Completo | Listado y detalle HTTP 200 |
| 14. Intervenciones de servicio | Completo | Listado HTTP 200 |
| 15. Repuestos de servicio | Completo | Listado HTTP 200 |
| 16. Pagos de servicio | Completo | Formulario HTTP 200; rechazo de monto 0, negativo y superior al saldo; pago válido y saldo 0 verificados |
| 17. Garantías | Completo | Listado y formulario HTTP 200 |
| 18. Reportes | Completo | Reporte general y filtrado por fechas HTTP 200 |
| 19. Usuarios y roles | Completo | Formulario HTTP 200 (solo Gerente); permisos aplicados |

## 4. Dashboard por rol

- **Gerente:** ventas de hoy y del mes, cobros de ventas y servicios, órdenes
  activas, clientes, productos, stock bajo, garantías vigentes, gráfico de los
  últimos 7 días, ventas y órdenes recientes, ventas con saldo pendiente,
  órdenes de servicio con saldo y garantías próximas a vencer.
- **Vendedor:** ventas de hoy y del mes, cobros de ventas del día y del mes,
  clientes activos y nuevos del mes, stock bajo, ventas con saldo pendiente,
  ventas y órdenes recientes, accesos rápidos a sus módulos.
- **Técnico:** órdenes activas, órdenes abiertas asignadas, recibidas hoy,
  cobros de servicios del día y del mes, estado del taller por estado, sus
  órdenes abiertas con saldo, repuestos con stock bajo y órdenes recientes.

## 5. Base de datos e integridad

- 20 tablas en `dbAlexasys`, incluida `consultas_contacto`.
- Comprobaciones ejecutadas:
  - `MovimientoInventario::verificacionStock()`: **0 discrepancias** en 12 productos.
  - Stock mínimo global: **2** (no hay stock negativo).
  - Ventas confirmadas: saldo = total − pagos confirmados.
  - Órdenes de servicio: saldo = mano de obra + repuestos − pagos confirmados.
  - Sin sobrepagos en ventas ni en servicios.

## 6. Pendientes

1. Pruebas de regresión completas de alta/edición/anulación en los módulos
   4–14 y 16–19 (en esta revisión se verificaron páginas, búsquedas y el flujo
   de pagos de servicio).
2. Prueba manual de generación de garantías desde una venta y desde una orden.
3. Prueba de carga de imágenes de producto (JPG/PNG/WEBP, límite 2 MB).
4. Configurar credenciales de producción y desactivar `demo.activo`.
5. Sustituir los datos de demostración por datos reales antes de la entrega.
6. Añadir una suite automatizada de pruebas (no existe actualmente).
7. Configurar el relay SMTP de XAMPP para que las notificaciones del formulario lleguen al correo destino.
