<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../conexion_BBDD/conexion_db_pm.php';

// Verificar si el usuario puede editar/borrar (Admin o Presidente)
$usuarioPuedeEditar = isset($_SESSION['id_usuario']) && isset($_SESSION["nombre_rol"]) && in_array($_SESSION["nombre_rol"], ["Admin", "Presidente"]);

if (isset($_GET['id'])) {
    $id_noticia = intval($_GET['id']); // Evitar inyecciones

    $sql = "SELECT * FROM noticias WHERE id_noticias = $id_noticia";
    $resultado = $pdo->query($sql);

    if ($resultado->rowCount() > 0) {
        $noticia = $resultado->fetch();

        // Verificar si la imagen está vacía y asignar la ruta por defecto si es necesario
        $imagen = !empty($noticia['imagen']) ? $noticia['imagen'] : '/Proyecto-Comunidad/web/etc/assets/img/bloque.jpg';

        // Escapar la ruta de la imagen
        $imagen = htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8');

        echo "
            
            <div class='noticia-header'>
                <div class='noticia-imagen'>
                    <img src='" . $imagen . "' alt='Imagen de la noticia'>
                </div>
                <div class='noticia-info'>
                    <h2 class='titulo-noticia'>" . htmlspecialchars($noticia['titulo']) . "</h2>
                    <p class='detalle-noticia'>" . date('d/m/Y', strtotime($noticia['fecha'])) . "</p>
                </div>
            </div>

            <div class='descripcion-noticia'>
                <p>" . nl2br(htmlspecialchars($noticia['contenido'])) . "</p>
            </div>
        ";

        // Mostrar botones si es admin o presidente
        if ($usuarioPuedeEditar) {
            echo "
                <div class='botones-admin'>
                    <form method='POST' action='../../../backend/src/noticias/eliminar_noticia.php' onsubmit='return confirmarBorrado(event)'>
                        <input type='hidden' name='id_noticias' value='$id_noticia'>
                        <button type='submit' class='boton-noticia'>Borrar noticia</button>
                    </form>
                    <form method='GET' action='../../../web/src/noticias/editar_noticia.php'>
                        <input type='hidden' name='id' value='$id_noticia'>
                        <button type='submit' class='boton-noticia'>Editar noticia</button>
                    </form>
                </div>
            ";
        }

    } else {
        echo "<p>Noticia no encontrada.</p>";
    }
} else {
    echo "<p>ID de la noticia no proporcionado.</p>";
}
?>
