# Alex@sys

Sistema de Información Web para la gestión de clientes, ventas, inventario y
servicio técnico. Desarrollado en PHP 8.2 con arquitectura MVC, PDO y MySQL/
MariaDB, sin frameworks de backend.

## 1. Tecnologías

| Componente | Versión / uso |
|---|---|
| PHP | 8.2 o superior (probado con 8.2.12, extensiones `pdo_mysql`, `mbstring`, `fileinfo`) |
| Base de datos | MySQL 8 / MariaDB 10.4+ (probado con MariaDB 10.4.32) |
| Acceso a datos | PDO con consultas preparadas reales (`ATTR_EMULATE_PREPARES = false`) |
| Interfaz | Bootstrap 5 y Font Awesome 6 locales (`assets/vendor`) |
| Servidor de desarrollo | `php -S 127.0.0.1:8080 -t .` |

## 2. Requisitos

- PHP 8.2+ con `pdo_mysql`.
- MySQL o MariaDB en ejecución.
- Permisos de escritura en `logs/` e `imagen/productos/` (carga de imágenes).
- Navegador moderno.

## 3. Instalación

1. Copie la carpeta `MVC1` dentro del directorio público del servidor
   (por ejemplo `C:\xampp\htdocs\alexsys`) o use el servidor embebido de PHP.
2. Cree la base de datos e importe el esquema con datos de demostración:

   ```powershell
   C:\xampp\mysql\bin\mysql.exe -u root < database\alexsys.sql
   ```

   El script crea `dbAlexasys`, sus 20 tablas y datos de ejemplo (37 productos).
   **Es destructivo**: elimina y vuelve a crear las tablas del esquema.
3. Ajuste las credenciales en `config/configuracion.php` (`db.host`, `db.puerto`,
   `db.nombre`, `db.usuario`, `db.password`).
   La dirección destinataria del formulario se configura en
   `contacto.correo_destino` y el envío de correo se activa o desactiva con
   `contacto.notificar_por_correo`. Para una base existente, aplique
   `database/actualizacion-consultas-contacto.sql` y
   `database/actualizacion-catalogo.sql` sin reimportar el esquema.
4. Inicie el servidor:

   ```powershell
   cd MVC1
   C:\xampp\php\php.exe -S 127.0.0.1:8080 -t .
   ```

5. Abra `http://127.0.0.1:8080/index.php`.

> En producción, coloque `config/`, `logs/`, `database/` y `tests/` fuera del
> directorio público o bloquéelos desde el servidor web. Los archivos
> `.htaccess` incluidos ya niegan el acceso directo a `Core/`, `Controllers/`,
> `Models/`, `Views/`, `config/`, `logs/`, `database/` y `tests/`.

## 4. Usuarios de demostración

| Usuario | Contraseña | Rol |
|---|---|---|
| `gerente` | `Admin@123` | Gerente |
| `vendedor` | `Venta@123` | Vendedor |
| `tecnico` | `Tecnico@123` | Técnico |

Cambie estas contraseñas antes de operar el sistema. El aviso de datos de
demostración se controla con `demo.activo` en `config/configuracion.php`.

## 5. Estructura del proyecto

```
MVC1/
├── index.php                 Front controller (?ruta=modulo)
├── .htaccess                 Reglas de escritura y protecciones
├── Core/
│   ├── bootstrap.php         Configuración, sesión, helpers, permisos y vistas
│   └── Database/conexion.php Conexión PDO única (singleton)
├── config/configuracion.php  Aplicación, base de datos, seguridad y demo
├── Controllers/              Un controlador por módulo
├── Models/                   Entidades y consultas PDO
├── Views/                    Vistas del panel y plantillas parciales
│   └── publico/              Inicio, Nosotros, Productos, Servicios, Contactos
├── assets/                   CSS, JS y librerías locales
├── imagen/                   Logo, banners, fotos e iconos SVG
│   └── productos/            Fotos: accesorios/, escritorio/, portatiles/
├── database/alexsys.sql      Esquema y datos de demostración (20 tablas)
├── docs/                     Manuales y documentación
├── logs/                     Errores de PHP y del servidor
└── tests/                    Espacio reservado para pruebas (sin acceso web)
```

## 6. Módulos

1. Inicio y sitio público (Inicio, Nosotros, Productos, Servicios, Contactos).
2. Acceso, sesiones, bloqueo por intentos y cierre de sesión.
3. Dashboard adaptable a Gerente, Vendedor y Técnico.
4. Clientes.
5. Categorías.
6. Productos.
7. Proveedores.
8. Compras e ingreso de inventario.
9. Inventario y ajustes con movimiento obligatorio.
10. Ventas y comprobantes.
11. Pagos de ventas.
12. Equipos de clientes.
13. Órdenes de servicio.
14. Detalle e intervenciones de servicio.
15. Repuestos usados en servicio.
16. Pagos de servicios.
17. Garantías.
18. Reportes.
19. Usuarios y roles.
20. Bandeja **Consultas web** (solo Gerente): lee los mensajes del formulario
    público aunque el servidor no tenga correo configurado.

## 7. Reglas de negocio principales

- **Inventario:** toda variación de stock genera un movimiento
  (`Entrada`/`Salida`) con referencia al documento de origen. El stock no puede
  quedar negativo (`MovimientoInventario::registrar()` y `ajustar()`).
- **Compras:** al confirmar una compra se actualiza stock y se registra el
  movimiento correspondiente.
- **Ventas:** al confirmar una venta se descuenta stock y se registra el
  movimiento; una venta anulada reintegra el stock.
- **Pagos:** `SALDO = TOTAL - PAGOS CONFIRMADOS`. No se aceptan pagos mayores al
  saldo, montos cero ni negativos, ni pagos sobre documentos anulados.
- **Garantías:** se generan por venta de producto o por orden de servicio y
  tienen vigencia controlada por fecha.
- **Permisos:** cada rol tiene módulos visibles y módulos de escritura; el
  Gerente tiene acceso total.

## 8. Seguridad

- Contraseñas con `password_hash()` / `password_verify()`.
- Tokens CSRF en formularios (`csrf_field()` y `csrf_verificar()`).
- Escape de salida con `e()` en las vistas.
- Consultas preparadas con parámetros únicos (sin reutilizar marcadores).
- Control de sesión por inactividad y bloqueo temporal por intentos fallidos.
- Validación de archivos subidos (tipo, tamaño y dimensiones de imagen).

## 9. Pruebas

El detalle de las pruebas ejecutadas y su evidencia está en
[`TESTING.md`](TESTING.md). El estado de avance por módulo está en
[`PROGRESS.md`](PROGRESS.md).

La suite de humo se ejecuta con el servidor de desarrollo activo:

```powershell
powershell -ExecutionPolicy Bypass -File tests\smoke.ps1
```

## 10. Publicación

Este proyecto necesita **PHP 8.2+ y MySQL/MariaDB**. Por eso **GitHub Pages no
puede mostrarlo**: Pages solo sirve archivos estáticos (HTML, CSS, JS) y no
ejecuta PHP ni conecta bases de datos. El repositorio puede estar completo y
aun así la URL de Pages devolver 404.

Para publicarlo elija un hosting con PHP y MySQL, por ejemplo:

1. **XAMPP/local** para la demostración: copie `MVC1` en `htdocs`, importe
   `database/alexsys.sql` e ingrese por `http://localhost/MVC1/`.
2. **Hosting PHP compartido** (cPanel): suba el contenido de `MVC1`, cree la
   base, importe el SQL y ajuste `config/configuracion.php`.
3. **Servicios con contenedores** (Railway, Render, Fly.io): configure un
   servicio PHP 8.2 y una base MySQL/MariaDB; use las variables del proveedor en
   `config/configuracion.php`.

En producción desactive `demo.activo`, cambie las contraseñas de demostración y
mantenga `config/`, `logs/`, `database/` y `tests/` fuera del directorio público.

## 11. Documentación

- [`docs/INSTALACION.md`](docs/INSTALACION.md): instalación y puesta en marcha.
- [`docs/MANUAL_USUARIO.md`](docs/MANUAL_USUARIO.md): uso por rol.
- [`docs/RESPALDO.md`](docs/RESPALDO.md): respaldo y restauración.
- [`imagen/FUENTES.md`](imagen/FUENTES.md): origen de imágenes e iconos.
