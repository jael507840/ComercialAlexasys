# Origen de imágenes e iconos

Este documento registra la procedencia de los recursos gráficos del proyecto.
Las rutas son relativas a `MVC1/`.

## 1. Logotipo

| Archivo | Origen |
|---|---|
| `imagen/logo/logo-alexsys.svg` | Logotipo del proyecto Alex@sys |
| `imagen/logo/logo-alexsys-claro.svg` | Variante clara del mismo logotipo |
| `imagen/logo/favicon.svg` | Isotipo derivado del logotipo |

## 2. Fotografías

| Carpeta | Contenido | Origen |
|---|---|---|
| `imagen/banners/` | Banner de bienvenida, hero de tienda y banner de servicio | Material del proyecto |
| `imagen/nosotros/` | Tienda, equipo de trabajo y atención al cliente | Material del proyecto |
| `imagen/productos/` | Computadoras, laptops, periféricos, componentes e impresoras | Material del proyecto |
| `imagen/servicios/` | Mantenimiento, reparación, impresoras y configuración | Material del proyecto |
| `imagen/accesorios/` | Accesorios de catálogo | Material del proyecto |
| `imagen/computadoras/` | Equipos de catálogo | Material del proyecto |

Estas fotografías se incluyeron con el proyecto para fines académicos y de
demostración. No se descargaron de bancos de imágenes en esta revisión.

> Archivos vacíos (0 bytes), no referenciados por las vistas:
> `imagen/accesorios/combo-gamer-red-dragon.jpg`,
> `imagen/accesorios/mouse-inalambrico-genius.jpg`,
> `imagen/accesorios/ssd-1tb.jpg`,
> `imagen/computadoras/all-in-one-elite.jpg`,
> `imagen/computadoras/laptop-gamer-pro.jpg`,
> `imagen/computadoras/pc-escritorio-x10.jpg`.
> Se conservan como marcadores de posición; pueden eliminarse o reemplazarse
> por fotografías definitivas.

## 3. Iconos de módulos

Los iconos de `imagen/iconos/` son SVG elaborados para el proyecto, con un
estilo plano, fondo redondeado y trazo de 2.2–2.8 px:

| Archivo | Uso |
|---|---|
| `dashboard.svg` | Dashboard / panel principal |
| `clientes.svg` | Clientes (existente) |
| `ventas.svg` | Ventas (existente) |
| `compras.svg` | Compras (existente) |
| `inventario.svg` | Inventario (existente) |
| `servicio.svg` | Órdenes de servicio (existente) |
| `garantias.svg` | Garantías (existente) |
| `productos.svg` | Productos |
| `categorias.svg` | Categorías |
| `proveedores.svg` | Proveedores |
| `equipos.svg` | Equipos de clientes |
| `intervenciones.svg` | Detalle e intervenciones |
| `repuestos.svg` | Repuestos usados |
| `pagos.svg` | Pagos de ventas y servicios |
| `reportes.svg` | Reportes |
| `usuarios.svg` | Usuarios y roles |

Los seis iconos marcados como existentes ya formaban parte del proyecto y se
mantuvieron; los diez restantes se crearon en esta revisión con el mismo estilo.
Los archivos `dashboard.svg`, `productos.svg`, etc. se usan en el dashboard y en
las páginas públicas de Inicio y Nosotros.

## 4. Misión, visión y valores

| Archivo | Origen |
|---|---|
| `imagen/iconos/mision.svg` | Icono de misión elaborado para el proyecto (512 × 512) |
| `imagen/iconos/vision.png` | Imagen proporcionada por el propietario del proyecto (512 × 512) |
| `imagen/iconos/valores.svg` | Icono de valores elaborado para el proyecto (512 × 512) |

## 5. Librerías de interfaz

| Recurso | Versión | Licencia |
|---|---|---|
| Bootstrap (`assets/vendor/bootstrap.min.css`, `bootstrap.bundle.min.js`) | 5.3.3 | MIT |
| Font Awesome Free (`assets/vendor/fontawesome.min.css`, `webfonts/`) | 6.5.2 | Iconos: CC BY 4.0; fuentes: SIL OFL 1.1; código: MIT |

Ambas librerías se sirven localmente desde `assets/vendor/`; el sistema no
depende de CDN.

## 6. Uso

- Los iconos de módulos se muestran a 42 × 42 px en los accesos rápidos del
  dashboard y a 52 × 52 px en las tarjetas públicas.
- Los iconos de misión, visión y valores se muestran a 64 × 64 px.
- Las fotografías de producto usan `object-fit: cover` y altura fija de 170 px
  en las tarjetas del catálogo.
