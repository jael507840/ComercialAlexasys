# Respaldo y restauración

## 1. Qué respaldar

| Elemento | Ruta | Contenido |
|---|---|---|
| Base de datos | `dbAlexasys` | 20 tablas, incluidas las consultas del formulario de contacto |
| Código y configuración | carpeta `MVC1` | Controladores, modelos, vistas, CSS, JS |
| Imágenes | `imagen/` | Logo, fotos de productos, servicios, nosotros e iconos |
| Imágenes subidas | `imagen/productos/` | Archivos cargados por los usuarios |
| Configuración local | `config/configuracion.php` | Credenciales y parámetros |

Los respaldos deben guardarse fuera del directorio público y, preferiblemente,
en otra máquina o servicio de almacenamiento.

## 2. Respaldo de la base de datos

### Windows / XAMPP

```powershell
$fecha = Get-Date -Format "yyyyMMdd-HHmm"
C:\xampp\mysql\bin\mysqldump.exe -u root --single-transaction --routines --triggers `
  --default-character-set=utf8mb4 dbAlexasys > "C:\respaldos\dbAlexasys-$fecha.sql"
```

### Linux

```bash
fecha=$(date +%Y%m%d-%H%M)
mysqldump -u root -p --single-transaction --routines --triggers \
  --default-character-set=utf8mb4 dbAlexasys > "/var/backups/dbAlexasys-$fecha.sql"
```

Verifique que el archivo no esté vacío y que termine con la línea
`-- Dump completed`.

## 3. Respaldo de archivos

```powershell
$fecha = Get-Date -Format "yyyyMMdd-HHmm"
Compress-Archive -Path "C:\xampp\htdocs\alexsys\*" `
  -DestinationPath "C:\respaldos\alexsys-$fecha.zip"
```

Incluya `config/configuracion.php` y todo `imagen/`; excluya `logs/` si no
necesita conservar los errores históricos.

## 4. Restauración

> La restauración reemplaza los datos actuales. Detenga el sistema o ponga la
> aplicación en mantenimiento antes de continuar.

1. Cree una base limpia si es necesario:

   ```sql
   CREATE DATABASE dbAlexasys CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Importe el respaldo:

   ```powershell
   C:\xampp\mysql\bin\mysql.exe -u root dbAlexasys < "C:\respaldos\dbAlexasys-AAAAMMDD-HHMM.sql"
   ```

3. Restaure la carpeta de archivos e imágenes en su ubicación original.
4. Verifique permisos de escritura en `logs/` e `imagen/productos/`.
5. Inicie sesión y compruebe:
   - dashboard con datos;
   - ventas y saldos;
   - stock y movimientos de inventario;
   - órdenes y pagos de servicio;
   - carga de una imagen de producto.

## 5. Frecuencia sugerida

| Entorno | Base de datos | Archivos |
|---|---|---|
| Desarrollo | Antes de cada cambio importante | Antes de cada entrega |
| Producción | Diario (automático) | Semanal y antes de actualizar |

Conserve al menos 7 respaldos diarios y 4 semanales, y pruebe una restauración
completa al menos una vez al mes.

## 6. Antes de actualizar el sistema

1. Genere un respaldo de base de datos y archivos.
2. Anote la versión actual (`app.version` en `config/configuracion.php`).
3. Aplique la actualización.
4. Ejecute `tests/smoke.ps1` y revise `logs/php-error.log`.
5. Si algo falla, restaure el respaldo y la carpeta anterior.
