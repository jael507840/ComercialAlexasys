# Prueba de humo de Alex@sys (solo lectura).
# Uso: powershell -ExecutionPolicy Bypass -File tests\smoke.ps1
param(
    [string]$Base = "http://127.0.0.1:8080/index.php"
)

$ErrorActionPreference = "Stop"
$fallos = 0

function Login([string]$usuario, [string]$password, [string]$base) {
    $sesion = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $inicio = Invoke-WebRequest "$base`?ruta=login" -WebSession $sesion -UseBasicParsing
    $token = [regex]::Match($inicio.Content, 'name="csrf" value="([^"]+)"').Groups[1].Value
    if (-not $token) { throw "No se encontro el token CSRF del login." }
    try {
        Invoke-WebRequest "$base`?ruta=login" -Method Post `
            -Body @{ csrf = $token; usuario = $usuario; password = $password } `
            -WebSession $sesion -UseBasicParsing -MaximumRedirection 5 | Out-Null
    } catch {
        # El login redirige al dashboard; un error de redireccion no es fallo.
    }
    return $sesion
}

function Comprobar([string]$nombre, [bool]$condicion, [string]$detalle = "") {
    if ($condicion) {
        Write-Host "PASS  $nombre  $detalle"
    } else {
        Write-Host "FAIL  $nombre  $detalle"
        $script:fallos++
    }
}

function SinAvisos([string]$html) {
    return -not ($html -match '<b>(Warning|Fatal error|Notice|Deprecated)</b>')
}

$roles = @(
    @{ usuario = "gerente";  password = "Admin@123";  perfil = "text-bg-primary";   acceso = 16 },
    @{ usuario = "vendedor"; password = "Venta@123";  perfil = "text-bg-success";   acceso = 9 },
    @{ usuario = "tecnico";  password = "Tecnico@123"; perfil = "text-bg-info";      acceso = 10 }
)

foreach ($rol in $roles) {
    $sesion = Login $rol.usuario $rol.password $Base
    $panel = Invoke-WebRequest "$Base`?ruta=index" -WebSession $sesion -UseBasicParsing
    $iconos = ([regex]::Matches($panel.Content, 'class="acceso-rapido"[\s\S]{0,400}?imagen/iconos/[a-z]+\.(?:svg|png)')).Count
    $accesos = ([regex]::Matches($panel.Content, 'class="acceso-rapido"')).Count
    Comprobar "dashboard/$($rol.usuario)" ($panel.StatusCode -eq 200 -and (SinAvisos $panel.Content)) "HTTP $($panel.StatusCode), $($panel.RawContentLength) bytes"
    Comprobar "dashboard/$($rol.usuario)/perfil" ($panel.Content -match $rol.perfil) $rol.perfil
    Comprobar "dashboard/$($rol.usuario)/accesos" ($accesos -eq $rol.acceso -and $iconos -eq $rol.acceso) "accesos=$accesos iconos=$iconos"
}

# Rutas del panel como Gerente.
$gerente = Login "gerente" "Admin@123" $Base
$rutas = @(
    "clientes", "categorias", "productos", "proveedores", "compras", "inventario",
    "ventas", "pagos-venta", "equipos", "ordenes-servicio", "detalle-servicio",
    "repuestos-servicio", "pagos-servicio", "garantias", "garantias&accion=nuevo",
    "reportes", "registrar-usuario", "consultas-contacto"
)
foreach ($ruta in $rutas) {
    try {
        $r = Invoke-WebRequest "$Base`?ruta=$ruta" -WebSession $gerente -UseBasicParsing -TimeoutSec 30
        Comprobar "modulo/$ruta" ($r.StatusCode -eq 200 -and (SinAvisos $r.Content)) "HTTP $($r.StatusCode), $($r.RawContentLength) bytes"
    } catch {
        Comprobar "modulo/$ruta" $false $_.Exception.Message
    }
}

# Paginas publicas: estado y carga de iconos.
foreach ($pagina in @("inicio", "nosotros", "productos", "servicios", "contactos")) {
    $r = Invoke-WebRequest "$Base`?pagina=$pagina" -WebSession $gerente -UseBasicParsing
    Comprobar "publico/$pagina" ($r.StatusCode -eq 200 -and (SinAvisos $r.Content)) "HTTP $($r.StatusCode), $($r.RawContentLength) bytes"

    if ($pagina -eq "nosotros") {
        Comprobar "publico/nosotros/ubicacion" ($r.Content -match 'imagen/nosotros/ubicacion\.PNG') "imagen de ubicación incluida"
    }

    if ($pagina -eq "contactos") {
        Comprobar "publico/contactos/formulario" (
            $r.Content -match 'name="nombre"[^>]+required' -and
            $r.Content -match 'name="correo"[^>]+type="email"' -and
            $r.Content -match 'name="mensaje"[\s\S]{0,250}required'
        ) "campos obligatorios y correo validado"

        $token = [regex]::Match($r.Content, 'name="csrf" value="([^"]+)"').Groups[1].Value
        if (-not $token) { throw "No se encontro el token CSRF del formulario de contacto." }
        $respuestaInvalida = Invoke-WebRequest "$Base`?pagina=contactos" -Method Post `
            -Body @{ csrf = $token; nombre = "Jael123"; correo = "correo-invalido"; mensaje = "Consulta de prueba suficientemente larga" } `
            -WebSession $gerente -UseBasicParsing
        Comprobar "publico/contactos/validacion-servidor" (
            $respuestaInvalida.StatusCode -eq 200 -and
            $respuestaInvalida.Content -match 'Escriba un nombre usando solo' -and
            $respuestaInvalida.Content -match 'Escriba un correo'
        ) "rechaza nombre con números y correo inválido sin guardar"

        $nombreLargo = "A" * 101
        $mensajeLargo = "x" * 3001
        $respuestaLarga = Invoke-WebRequest "$Base`?pagina=contactos" -Method Post `
            -Body @{ csrf = $token; nombre = $nombreLargo; correo = "prueba@example.com"; mensaje = $mensajeLargo } `
            -WebSession $gerente -UseBasicParsing
        Comprobar "publico/contactos/limites-servidor" (
            $respuestaLarga.StatusCode -eq 200 -and
            $respuestaLarga.Content -match 'nombre no puede superar 100 caracteres' -and
            $respuestaLarga.Content -match 'mensaje no puede superar 3000 caracteres'
        ) "rechaza datos sobre el límite en vez de truncarlos y aceptarlos"
    }
}

# Permisos: un modulo no permitido debe terminar en el dashboard.
$prohibidas = @(
    @{ usuario = "vendedor"; password = "Venta@123";  ruta = "compras" },
    @{ usuario = "tecnico";  password = "Tecnico@123"; ruta = "ventas" }
)
foreach ($caso in $prohibidas) {
    $sesion = Login $caso.usuario $caso.password $Base
    try {
        $r = Invoke-WebRequest "$Base`?ruta=$($caso.ruta)" -WebSession $sesion -UseBasicParsing -MaximumRedirection 5
        Comprobar "permiso/$($caso.usuario)/$($caso.ruta)" ($r.Content -match 'Accesos r') "redirigido al dashboard"
    } catch {
        Comprobar "permiso/$($caso.usuario)/$($caso.ruta)" $false $_.Exception.Message
    }
}

Write-Host ""
if ($fallos -eq 0) {
    Write-Host "RESULTADO: OK (0 fallos)"
    exit 0
}
Write-Host "RESULTADO: $fallos fallo(s)"
exit 1
