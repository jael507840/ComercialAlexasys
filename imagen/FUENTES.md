# Origen de imágenes e iconos

Este documento registra la procedencia de los recursos gráficos del proyecto.
Las rutas son relativas a `MVC1/`.

## 1. Logotipo

| Archivo | Origen |
|---|---|
| `imagen/logo/logo1.jfif` | Logotipo actual, usado en la barra pública, el pie, el acceso y el panel administrativo |
| `imagen/logo/logo-alexsys.svg` | Logotipo vectorial anterior, conservado como respaldo |
| `imagen/logo/logo-alexsys-claro.svg` | Variante clara del logotipo anterior |
| `imagen/logo/favicon.svg` | Favicon del sitio |

El logotipo `logo1.jfif` tiene fondo blanco; en zonas oscuras se muestra dentro
del contenedor `.marca-logo-fondo` para mantener el contraste.

## 2. Fotografías

| Carpeta | Contenido | Origen |
|---|---|---|
| `imagen/banners/` | Banner de bienvenida, hero de tienda y banner de servicio | Material del proyecto |
| `imagen/nosotros/` | Tienda, equipo de trabajo y atención al cliente | Material del proyecto |
| `imagen/productos/accesorios/` | Monitores, teclados, mouse, auriculares, discos, adaptadores y redes | Material del proyecto |
| `imagen/productos/escritorio/` | Computadoras de escritorio, all in one y equipos gamer | Material del proyecto |
| `imagen/productos/portatiles/` | Laptops ASUS, HP, Lenovo y MSI | Material del proyecto |
| `imagen/productos/componentes.jpg` | Componentes y repuestos | Material del proyecto |
| `imagen/productos/impresora.jpg` | Impresora multifuncional | Material del proyecto |
| `imagen/servicios/` | Mantenimiento, reparación, impresoras y configuración | Material del proyecto |

Estas fotografías se incluyeron con el proyecto para fines académicos y de
demostración. No se descargaron de bancos de imágenes en esta revisión.

> Las 37 rutas de imagen usadas por el catálogo existen y fueron verificadas.
> Quedan algunas fotografías antiguas sin uso en la raíz de `imagen/productos/`
> (`all-in-one.jpg`, `laptop-*.jpg`, `laptop-env.png`); se conservan como
> respaldo y pueden eliminarse si no se necesitan.

> Las carpetas `imagen/accesorios/` e `imagen/computadoras/` contienen archivos
> vacíos (0 bytes) no referenciados por las vistas. Se conservan como marcadores
> de posición; pueden eliminarse o reemplazarse por fotografías definitivas.

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
| `consultas.svg` | Bandeja de consultas web |
| `reportes.svg` | Reportes |
| `usuarios.svg` | Usuarios y roles |

Los seis iconos marcados como existentes ya formaban parte del proyecto y se
mantuvieron; los once restantes se crearon en esta revisión con el mismo estilo.
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
