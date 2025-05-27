<?php
require_once __DIR__ . '/../../../config.php';
session_start();
include __DIR__ . '/../../../backend/src/conexion_BBDD/conexion_db_pm.php';

if (!isset($_SESSION["nombre_rol"]) || !in_array($_SESSION["nombre_rol"], ["Admin", "Presidente"])) {
    echo "<p>No tienes permiso para acceder a esta página.</p>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<p>ID del evento no proporcionado.</p>";
    exit;
}

$id_evento = intval($_GET['id']);
$sql = "SELECT * FROM eventos WHERE id_evento = $id_evento";
$result = $pdo->query($sql);

if ($result->rowCount() === 0) {
    echo "<p>Evento no encontrado.</p>";
    exit;
}

$evento = $result->fetch();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Evento</title>
    <link rel="stylesheet" href="crear_evento.css?<?= time(); ?>" />
</head>

<body class="fondo-cuerpo">


    <div class="contenedor-principal">
        <h2 class="titulo-evento">Editar Evento</h2>
        <form class="formulario-evento" method="POST" action="../../../backend/src/eventos/procesar_edicion_evento.php">
            <input type="hidden" name="id_evento" value="<?= $evento['id_evento']; ?>">

            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($evento['titulo']); ?>" required>

            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="5" required><?= htmlspecialchars($evento['descripcion']); ?></textarea>

            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="<?= $evento['fecha']; ?>" required>

            <div class="checkbox-destacado">
                <label for="descripcion">¿El evento es destacado?</label> <br>
                <label><input type="radio" name="es_destacada" value="1" <?= $evento['es_destacada'] == 1 ? 'checked' : ''; ?>> Sí</label>
                <label><input type="radio" name="es_destacada" value="0" <?= $evento['es_destacada'] == 0 ? 'checked' : ''; ?>> No</label>
            </div>

            <div class="botones-formulario">
                <button type="submit" class="boton-evento">Guardar Cambios</button>
                <a href="/../Proyecto-Comunidad/web/src/eventos/index.php" class="boton-evento boton-secundario">Volver a eventos</a>
            </div>

        </form>
    </div>
</body>

</html>