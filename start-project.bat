@echo off
setlocal
cd /d "%~dp0"

set PHP_BIN=C:\xampp\php\php.exe
if not exist "%PHP_BIN%" set PHP_BIN=C:\Program Files\PHP84\php.exe
if not exist "%PHP_BIN%" (
    echo No se encontr^o PHP en las rutas esperadas.
    echo Instala XAMPP o PHP 8.2+ con PDO MySQL y vuelve a intentar.
    pause
    exit /b 1
)

for /f "tokens=5" %%A in ('netstat -ano ^| findstr :8080') do (
    set "PID=%%A"
)

if not "%PID%"=="" (
    echo El puerto 8080 ya est^a en uso.
    echo Cierra la otra instancia o usa un puerto diferente.
    pause
    exit /b 1
)

echo Iniciando Alex@sys...
start "" /B "%PHP_BIN%" -S 127.0.0.1:8080 -t .

ping -n 2 127.0.0.1 > nul
start "" http://127.0.0.1:8080/index.php

echo.
echo Proyecto disponible en: http://127.0.0.1:8080/index.php

echo Usuarios demo:
 echo - gerente / Admin@123
 echo - vendedor / Venta@123
 echo - tecnico / Tecnico@123

echo.
choice /C SN /M "^Quieres mantener esta ventana abierta? [S/N]"
if errorlevel 2 exit /b 0
if errorlevel 1 goto :keepopen

:keepopen
pause
