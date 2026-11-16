<?php
/** Cabecera común del sitio público. Variables opcionales: $titulo, $descripcion. */
$titulo = $titulo ?? 'Alex@sys';
$descripcion = $descripcion ?? 'Sistema de gestión de clientes, ventas, inventario y servicio técnico de Alex@sys.';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($descripcion) ?>">
    <title><?= e($titulo) ?> · Alex@sys</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('imagen/logo/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/estilos.css') ?>">
</head>
<body>
