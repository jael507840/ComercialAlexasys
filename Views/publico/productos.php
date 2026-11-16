<?php /** Página pública: Productos. Variables: $productos, $categorias, $busqueda, $idCategoria. */ ?>
<section class="bg-white border-bottom">
    <div class="container py-5">
        <nav aria-label="Ruta de navegación">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="<?= url('index') ?>">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Productos</li>
            </ol>
        </nav>
        <h1 class="seccion-titulo h2 mb-2">Catálogo de productos</h1>
        <p class="seccion-subtitulo mb-4">
            Equipos, accesorios, componentes e impresoras disponibles en tienda.
            Los precios se expresan en bolivianos e incluyen el comprobante interno de venta.
        </p>

        <form class="row g-2 align-items-end" method="get" action="<?= asset('index.php') ?>">
            <input type="hidden" name="ruta" value="index">
            <input type="hidden" name="pagina" value="productos">
            <div class="col-md-5">
                <label class="form-label" for="q">Buscar producto</label>
                <input class="form-control" id="q" name="q" type="search" value="<?= e($busqueda) ?>"
                       placeholder="Código, nombre, marca o modelo">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="categoria">Categoría</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="0">Todas las categorías</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= (int) $categoria['id_categoria'] ?>"
                            <?= $idCategoria === (int) $categoria['id_categoria'] ? 'selected' : '' ?>>
                            <?= e($categoria['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
                </button>
                <a class="btn btn-outline-secondary" href="<?= url('index', ['pagina' => 'productos']) ?>" title="Limpiar filtros">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</section>

<section class="seccion">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="text-secondary small">
                <?= count($productos) ?> producto(s) encontrado(s)
            </span>
        </div>
        <?php if ($productos === []): ?>
            <div class="alert alert-info">
                <i class="fa-solid fa-circle-info me-2"></i>
                No se encontraron productos con los criterios indicados. Intente con otra búsqueda.
            </div>
        <?php endif; ?>

        <?php
        // Agrupa el catálogo por categoría para que los equipos y accesorios
        // aparezcan ordenados por su uso: escritorio, portátiles, accesorios, etc.
        $grupos = [];
        foreach ($productos as $producto) {
            $grupos[$producto['categoria'] ?? 'Otros'][] = $producto;
        }
        ?>
        <?php foreach ($grupos as $categoriaNombre => $items): ?>
            <div class="grupo-catalogo">
                <h2 class="grupo-catalogo-titulo h5"><?= e($categoriaNombre) ?></h2>
                <span class="badge text-bg-light border"><?= count($items) ?> producto(s)</span>
            </div>
            <div class="row g-4 mb-4">
                <?php foreach ($items as $producto): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card tarjeta-producto h-100 shadow-sm border-0">
                            <img src="<?= asset($producto['imagen'] ?: 'imagen/productos/componentes.jpg') ?>"
                                 alt="<?= e($producto['nombre']) ?>">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <span class="badge text-bg-light border"><?= e($producto['codigo']) ?></span>
                                    <span class="small text-secondary"><?= e($producto['categoria'] ?? '') ?></span>
                                </div>
                                <h3 class="h6 fw-bold mb-1"><?= e($producto['nombre']) ?></h3>
                                <div class="small text-secondary mb-2">
                                    <?= e(trim(($producto['marca'] ?? '') . ' ' . ($producto['modelo'] ?? ''))) ?>
                                </div>
                                <p class="small text-secondary flex-grow-1"><?= e($producto['descripcion'] ?? '') ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="fs-5 fw-bold text-primary"><?= e(formato_moneda($producto['precio_venta'])) ?></span>
                                    <?php if ((int) $producto['stock_actual'] > 0): ?>
                                        <span class="badge text-bg-success">Stock: <?= (int) $producto['stock_actual'] ?></span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Consultar stock</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="alert alert-light border mt-4 mb-0 small text-secondary">
            <i class="fa-solid fa-circle-info me-1"></i>
            La disponibilidad mostrada corresponde al inventario registrado. Para confirmar una compra,
            comuníquese con la tienda o acérquese al establecimiento.
        </div>
    </div>
</section>
