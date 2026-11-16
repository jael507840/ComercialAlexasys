<?php
/** Sitio público. Variables: $pagina, $productos, $categorias, $busqueda, $idCategoria. */
parcial('head', ['titulo' => $titulo]);
parcial('navbar-publico');
?>
<main>
    <?php require ROOT_PATH . '/Views/publico/' . $pagina . '.php'; ?>
</main>
<?php parcial('pie-publico'); ?>
