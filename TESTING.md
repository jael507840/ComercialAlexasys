# Pruebas de Alex@sys

**Última ejecución de humo:** 16/11/2026
**Entorno:** Windows, PHP 8.2.12 (`C:\xampp\php\php.exe`), MariaDB 10.4.32,
servidor embebido `php -S 127.0.0.1:8080 -t .`, base `dbalexasys` (20 tablas).
**Alcance:** prueba de humo de navegación y permisos, lint de PHP, integridad de
inventario, saldos, flujo de pagos de servicio y exportación de reportes a PDF
con Dompdf 3.1.6.

> Las pruebas son manuales/semiautomáticas y se ejecutaron contra el entorno
> local. No se declaran pruebas no ejecutadas.

## 1. Preparación

```powershell
# 1. Iniciar MySQL/MariaDB (XAMPP)
Start-Process "C:\xampp\mysql\bin\mysqld.exe" `
  -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini","--standalone" `
  -WindowStyle Hidden

# 2. Importar el esquema y datos de demostración (destructivo)
cd MVC1
C:\xampp\mysql\bin\mysql.exe -u root < database\alexsys.sql

# 3. Instalar dependencias (Dompdf)
$env:PATH = "C:\xampp\php;" + $env:PATH
composer install --no-interaction --no-progress

# 4. Iniciar el servidor de desarrollo
C:\xampp\php\php.exe -S 127.0.0.1:8080 -t .
```

## 2. Lint de PHP

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
  C:\xampp\php\php.exe -l $_.FullName
}
```

**Resultado:** 93 archivos PHP revisados (sin contar `vendor/`), **0 errores de
sintaxis**.

## 3. Prueba de humo

```powershell
powershell -ExecutionPolicy Bypass -File tests\smoke.ps1
```

La prueba inicia sesión con los tres usuarios, comprueba el dashboard, los
módulos del panel (incluida la bandeja **Consultas web**), las páginas públicas,
la imagen de ubicación, el catálogo agrupado, el formulario de contacto
(incluidos datos inválidos y límites) y los permisos denegados. Las pruebas del
formulario no guardan consultas ni envían correos. La salida completa quedó en
`tests/smoke-resultado.txt`.

**Resultado:** **52 comprobaciones PASS, 0 FAIL.**

| Grupo | Comprobaciones | Resultado |
|---|---|---|
| Dashboard por rol | 9 (estado, perfil, accesos e iconos) | 9 PASS |
| Módulos del panel (Gerente) | 18 | 18 PASS |
| Reportes, resumen y exportación PDF | 14 (resumen, botón, 11 informes y PDF filtrado) | 14 PASS |
| Páginas públicas y formulario de contacto | 9 | 9 PASS |
| Permisos denegados (Vendedor/Técnico) | 2 | 2 PASS |

Detalle destacado:

| Caso | Evidencia |
|---|---|
| Dashboard Gerente | HTTP 200, 43 484 bytes, perfil `text-bg-primary`, 16 accesos e iconos |
| Dashboard Vendedor | HTTP 200, 35 022 bytes, perfil `text-bg-success`, 9 accesos e iconos |
| Dashboard Técnico | HTTP 200, 31 160 bytes, perfil `text-bg-info`, 10 accesos e iconos |
| Bandeja Consultas web | HTTP 200, 21 303 bytes, 3 mensajes y 3 avisos pendientes; correo configurado `contacto@alexsys.bo` |
| Avisos PHP en las respuestas HTTP | 0 (`Warning`, `Fatal error`, `Notice`, `Deprecated`) |
| Vendedor accede a `compras` | Redirigido al dashboard |
| Técnico accede a `ventas` | Redirigido al dashboard |
| Reporte de ventas | HTTP 200, 25 414 bytes; tarjetas Registros 2, Total vendido Bs 4.920,00, Pagado Bs 4.920,00, Saldo Bs 0,00 |
| Exportación PDF (11 informes) | HTTP 200, `Content-Type: application/pdf`, firma `%PDF-`; tamaños entre 24 189 y 37 120 bytes |
| PDF con filtros de periodo | HTTP 200, 28 099 bytes, `%PDF-`; filtros `desde`/`hasta` conservados en la URL |

Comprobaciones adicionales del cambio:

- `imagen/nosotros/ubicacion.PNG`: HTTP 200.
- El catálogo público muestra **37 productos** agrupados en 5 categorías
  (8 escritorio, 9 laptops, 13 accesorios, 6 componentes/repuestos y 1 impresora);
  las 37 rutas de imagen existen en `imagen/productos/`.
- El formulario rechaza nombres con números, correos inválidos y campos que
  exceden los límites.
- El modelo de consultas insertó y actualizó el estado de notificación dentro
  de una transacción revertida; no quedaron datos de prueba en la base.
- En la bandeja **Consultas web** se verificaron 3 mensajes guardados, 3 avisos
  pendientes y el buzón `contacto@alexsys.bo`; la página no generó errores de
  consola.
- Los archivos PHP modificados pasaron `php -l`.
- Composer instaló `dompdf/dompdf` 3.1.6 y sus dependencias; `vendor/autoload.php`
  carga las clases `Dompdf\Dompdf` y `Dompdf\Options`.
- `Views/reportes-pdf.view.php` genera el documento A4 horizontal con cabecera,
  filtros aplicados, tarjetas de resumen, tabla paginada y pie con número de
  página; todas las respuestas se validaron por firma `%PDF-` y tipo MIME.
- El botón **Exportar PDF** conserva `tipo_reporte`, periodo, búsqueda, cliente,
  proveedor, categoría, estado, tipo y stock bajo.
- La inspección visual del PDF en un lector externo quedó pendiente: el
  navegador embebido no previsualiza la descarga porque la respuesta usa
  `Content-Disposition: attachment`. La validación fue por HTTP, firma y tamaño.
- El envío real de correo no se probó: XAMPP no tiene un relay SMTP local activo.
  Las consultas se guardan aunque la notificación por correo falle y se leen en
  la bandeja del panel.

## 4. Iconos e interfaz

Comprobación en navegador (viewport 1000 × 700) de las imágenes cargadas:

| Página | Iconos comprobados | Resultado |
|---|---|---|
| Dashboard (Gerente) | 16 iconos de módulos | Todos con `naturalWidth > 0`; 42 × 42 px |
| Inicio | `productos.svg`, `servicio.svg`, `garantias.svg`, `clientes.svg` | Todos cargados; 52 × 52 px |
| Nosotros | `mision.svg`, `vision.png`, `valores.svg` | Todos cargados; 64 × 64 px |

- Sin desbordamiento horizontal (`scrollWidth` ≤ viewport) en Dashboard, Inicio y
  Nosotros.
- Los accesos rápidos se distribuyen en tarjetas de altura uniforme por fila.

## 5. Integridad de inventario

Consulta ejecutada con `MovimientoInventario::verificacionStock()`:

```
productos=37 discrepancias=0
stock_minimo_global=2
```

- **0 discrepancias** entre `stock_actual` y la suma de entradas/salidas.
- **Ningún stock negativo** (mínimo global: 2).

## 6. Saldos y pagos

Consulta SQL de control (pagos confirmados):

| Documento | Total | Pagado | Saldo |
|---|---|---|---|
| V-000001 | 4600,00 | 4600,00 | 0,00 |
| V-000002 | 320,00 | 320,00 | 0,00 |
| OS-000001 | 210,00 | 100,00 | 110,00 |

- `SALDO = TOTAL − PAGOS CONFIRMADOS` se cumple en ventas y órdenes.
- Sobrepagos detectados: **0** en ventas y **0** en servicios.

## 7. Flujo de pagos de servicio (módulo 16)

Orden usada: `OS-000001` (total 210,00; pagado 100,00; saldo 110,00).
Usuario: `tecnico`.

| Caso | Resultado esperado | Resultado obtenido |
|---|---|---|
| Abrir formulario de pago | HTTP 200, monto máximo 110,00 | HTTP 200, `max="110.00"`, 0 avisos |
| Monto 200 (supera saldo) | Rechazo con mensaje de saldo | Rechazado; mensaje "supera el saldo" presente |
| Monto 0 | Rechazo | Rechazado; mensaje "mayor que cero" presente |
| Monto −5 | Rechazo | Rechazado; mensaje "mayor que cero" presente |
| Monto 110, método Transferencia | Pago confirmado, saldo 0 | Redirección a la orden; pago confirmado; saldo 0,00 |
| Orden y listado tras el pago | Saldo 0 y sin pendientes | Verificado |
| Dashboard Técnico tras el pago | Cobros de servicios hoy = Bs 110,00 | Verificado |

**Limpieza:** el pago de prueba (`referencia = QA-TEST`) se eliminó al terminar
y se restauró el estado de demostración (1 pago de Bs 100,00; saldo Bs 110,00).

## 8. Pruebas pendientes

Estas pruebas **no** se ejecutaron en esta revisión y quedan como trabajo futuro:

1. Alta, edición y anulación de clientes, productos, proveedores, compras,
   ventas, equipos, órdenes e intervenciones (más allá de la navegación).
2. Generación automática de garantías desde una venta y desde una orden.
3. Carga de imágenes de producto (JPG/PNG/WEBP, límite 2 MB).
4. Restauración de un respaldo en un entorno limpio.
5. Pruebas de concurrencia y doble envío de formularios.
6. Revisar visualmente los PDF en un lector externo (Adobe Acrobat, Edge o
   Chrome) para confirmar saltos de página y legibilidad con tablas extensas.
7. Ampliar la suite de humo (`tests/smoke.ps1`) con altas, ediciones y
   anulaciones; valorar PHPUnit para pruebas unitarias.
