# Instalación y puesta en marcha

## 1. Requisitos

- PHP 8.2 o superior con `pdo_mysql`, `mbstring` y `fileinfo`.
- MySQL 8 o MariaDB 10.4 o superior.
- Apache/Nginx o el servidor embebido de PHP.
- Permisos de escritura en `logs/` e `imagen/productos/`.

## 2. Instalación con XAMPP en Windows

1. Copie la carpeta `MVC1` en `C:\xampp\htdocs\alexsys` (o use la carpeta
   actual con el servidor embebido).
2. Inicie MySQL desde el panel de XAMPP o con:

   ```powershell
   Start-Process "C:\xampp\mysql\bin\mysqld.exe" `
     -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini","--standalone" `
     -WindowStyle Hidden
   ```

3. Importe el esquema:

   ```powershell
   cd C:\xampp\htdocs\alexsys
   C:\xampp\mysql\bin\mysql.exe -u root < database\alexsys.sql
   ```

4. Revise `config/configuracion.php`:

   ```php
   'db' => [
       'host'     => '127.0.0.1',
       'puerto'   => 3306,
       'nombre'   => 'dbAlexasys',
       'usuario'  => 'root',
       'password' => '',
       'charset'  => 'utf8mb4',
   ],
   'contacto' => [
       'correo_destino' => 'contacto@example.com',
   ],
   ```

   El formulario guarda cada consulta en `consultas_contacto` e intenta
   notificar a `contacto.correo_destino`. Para una base existente, aplique la
   migración sin volver a importar el esquema (la importación completa reinicia
   los datos):

   ```powershell
   Get-Content database\actualizacion-consultas-contacto.sql -Raw |
     & C:\xampp\mysql\bin\mysql.exe -u root
   ```

   El envío por correo necesita un relay SMTP configurado en PHP/XAMPP. Si no
   está disponible, la consulta permanece guardada y el sitio indica que no se
   pudo enviar la notificación. Configure el servidor SMTP localmente; no
   guarde contraseñas de correo en el repositorio.

5. Abra `http://localhost/alexsys/index.php` o, con el servidor embebido:

   ```powershell
   C:\xampp\php\php.exe -S 127.0.0.1:8080 -t .
   ```

   y visite `http://127.0.0.1:8080/index.php`.

## 3. Usuarios iniciales

| Usuario | Contraseña | Rol |
|---|---|---|
| `gerente` | `Admin@123` | Gerente |
| `vendedor` | `Venta@123` | Vendedor |
| `tecnico` | `Tecnico@123` | Técnico |

Cambie las contraseñas desde **Usuarios y roles** antes de usar el sistema con
datos reales.

## 4. Configuración de producción

1. Cambie `app.entorno` a `produccion`.
2. Desactive `demo.activo` para ocultar el aviso de datos de demostración.
3. Use un usuario de MySQL con permisos mínimos sobre `dbAlexasys` (no `root`).
4. Saque `config/`, `logs/`, `database/` y `tests/` del directorio público.
5. Configure HTTPS y respaldos automáticos.
6. Verifique que `logs/` e `imagen/productos/` sean escribibles solo por el
   usuario del servidor web.

## 5. Estructura de URL

Todas las solicitudes pasan por `index.php`:

```
index.php?ruta=login
index.php?ruta=index
index.php?ruta=clientes
index.php?ruta=ventas&accion=nuevo
index.php?pagina=productos
```

Las rutas permitidas están en la lista blanca de `index.php`; cualquier valor
desconocido se redirige al inicio.

## 6. Problemas comunes

| Síntoma | Causa probable | Solución |
|---|---|---|
| Error de conexión PDO | MySQL detenido o credenciales incorrectas | Inicie MySQL y revise `config/configuracion.php` |
| `HY093` en búsquedas | Marcadores repetidos con prepares reales | Use marcadores únicos (`:q1`, `:q2`, …) |
| Página en blanco | Error PHP | Revise `logs/php-error.log` |
| Iconos o fotos no cargan | Ruta o archivo faltante | Verifique `imagen/` y `imagen/FUENTES.md` |
| No se puede subir imagen | Tamaño mayor a 2 MB o formato no permitido | Use JPG, PNG o WEBP de hasta 2 MB |
