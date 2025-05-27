<?php
require_once __DIR__ . '/../../../config.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Noticia</title>
    <link rel="stylesheet" href="detalle.css?<?= time(); ?>" />
</head>

<body class="fondo-cuerpo">

<header>
    <?php include $_SERVER['DOCUMENT_ROOT'] . $basePath . 'web/src/header/cabecera.php'; ?>
</header>

<div class="contenedor-principal">

    <div class="contenedor-volver flotante">
        <form method="GET" action="index.php">
            <button type="submit" class="boton-noticia-volver">Volver a Noticias</button>
        </form>
    </div>

    <section class="detalle-evento-wrapper">
        <?php include '../../../backend/src/noticias/detalle.php'; ?>
    </section>

</div>

<!-- Modal de confirmación -->
<div id="modalConfirmacion" class="modal">
    <div class="modal-contenido">
        <p>¿Estás seguro de que quieres borrar esta noticia?</p>
        <div class="modal-botones">
            <button id="confirmarBtn">Sí, borrar</button>
            <button id="cancelarBtn">Cancelar</button>
        </div>
    </div>
</div>

<footer>
    <iframe src="../footer/FOOTER.html" frameborder="0" width="100%" height="300px"></iframe>
</footer>

<script>
    let formularioAEliminar = null;

    function confirmarBorrado(event) {
        event.preventDefault();
        formularioAEliminar = event.target;
        document.getElementById('modalConfirmacion').style.display = 'block';
        return false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('confirmarBtn').addEventListener('click', function () {
            document.getElementById('modalConfirmacion').style.display = 'none';
            if (formularioAEliminar) formularioAEliminar.submit();
        });

        document.getElementById('cancelarBtn').addEventListener('click', function () {
            document.getElementById('modalConfirmacion').style.display = 'none';
            formularioAEliminar = null;
        });
    });
</script>

</body>

</html>
