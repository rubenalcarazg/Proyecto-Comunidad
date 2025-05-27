<?php
require_once __DIR__ . '/../../../config.php';
session_start();
include __DIR__ . '/../../../backend/src/conexion_BBDD/conexion_db_pm.php';

if (!isset($_SESSION["nombre_rol"]) || !in_array($_SESSION["nombre_rol"], ["Admin", "Presidente"])) {
    echo "<p>No tienes permiso para acceder a esta página.</p>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<p>ID del noticia no proporcionado.</p>";
    exit;
}

$id_noticia = intval($_GET['id']);
$sql = "SELECT * FROM noticias WHERE id_noticias = $id_noticia";
$result = $pdo->query($sql);

if ($result->rowCount() === 0) {
    echo "<p>Noticia no encontrada.</p>";
    exit;
}

$noticia = $result->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Noticia</title>
    <link rel="stylesheet" href="crear_noticia.css?<?= time(); ?>" />
</head>
<body class="fondo-cuerpo">
    <div class="contenedor-principal">
        <h2 class="titulo-noticia">Editar Noticia</h2>
        <form class="formulario-noticia" method="POST" action="../../../backend/src/noticias/procesar_edicion_noticia.php">
            <input type="hidden" name="id_noticias" value="<?= $noticia['id_noticias']; ?>">

            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($noticia['titulo']); ?>" required>

            <label for="contenido">Descripción</label>
            <textarea id="contenido" name="contenido" rows="5" required><?= htmlspecialchars($noticia['contenido']); ?></textarea>

            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="<?= $noticia['fecha']; ?>" required>

            <div class="checkbox-destacado">
                <label for="descripcion">¿La noticia es destacada?</label> <br>
                <label><input type="radio" name="es_destacada" value="1" <?= $noticia['es_destacada'] == 1 ? 'checked' : ''; ?>> Sí</label>
                <label><input type="radio" name="es_destacada" value="0" <?= $noticia['es_destacada'] == 0 ? 'checked' : ''; ?>> No</label>
            </div>

            <div class="botones-formulario">
                <button type="submit" class="boton-noticia">Guardar Cambios</button>
                <a href="/../Proyecto-Comunidad/web/src/noticias/index.php" class="boton-noticia boton-secundario">Volver a noticias</a>
            </div>
        </form>
    </div>

    <footer>
        <iframe src="../footer/FOOTER.html" frameborder="0" width="100%" height="300px"></iframe>
    </footer>
</body>
</html>
