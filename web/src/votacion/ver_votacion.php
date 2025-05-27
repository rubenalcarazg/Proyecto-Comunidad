<?php

session_start();

$basePath = '/Proyecto-Comunidad/'; 

// Incluye el archivo de conexión a la base de datos
require_once __DIR__ . '/../../../backend/src/conexion_BBDD/conexion_db_pm.php'; 

// --- LÓGICA PARA VERIFICAR SI EL USUARIO ESTÁ LOGUEADO Y ES ADMINISTRADOR ---
$es_administrador = false;
$id_usuario_actual = null; // Para almacenar el ID del usuario logueado

// Redirecciona si el usuario no ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . $basePath . "web/src/login/index.php");
    exit();
}

$id_usuario_actual = $_SESSION['id_usuario'];

// *** CAMBIO CLAVE AQUÍ: Usamos $_SESSION["nombre_rol"] ***
if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Admin') {
    $es_administrador = true;
} else {
   
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votaciones Activas</title>
    <link rel="stylesheet" href="<?= $basePath ?>web/src/home/home.css">
    <link rel="stylesheet" href="<?= $basePath ?>web/src/foro/foro.css">
    <link rel="stylesheet" href="<?= $basePath ?>web/src/votacion/ver_votacion.css"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
</head>
<body>
    <header>
        <?php include __DIR__ . '/../header/cabecera.php'; ?>
    </header>

    <main id="votaciones-content">
        <h2>Votaciones Activas</h2>

        <?php
        // Mensajes de feedback
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo '<div class="mensaje-exito">¡Votación creada exitosamente!</div>';
        } elseif (isset($_GET['success']) && $_GET['success'] == 'votacion_eliminada') {
            echo '<div class="mensaje-exito">¡Votación eliminada exitosamente!</div>';
        } elseif (isset($_GET['error']) && $_GET['error'] == 'eliminacion_fallida') {
            echo '<div class="mensaje-error">Error al intentar eliminar la votación. Por favor, inténtalo de nuevo.</div>';
        } elseif (isset($_GET['error']) && $_GET['error'] == 'permisos_insuficientes') {
            echo '<div class="mensaje-error">No tienes permisos para realizar esta acción.</div>';
        } elseif (isset($_GET['error']) && $_GET['error'] == 'id_votacion_invalido') {
            echo '<div class="mensaje-error">ID de votación inválido.</div>';
        } elseif (isset($_GET['error']) && $_GET['error'] == 'solicitud_invalida') {
            echo '<div class="mensaje-error">Solicitud de eliminación inválida.</div>';
        }
        ?>

        <div id="votaciones-list">
            <?php
            try {
                
                $now = date('Y-m-d H:i:s');

                // Consulta para obtener las votaciones activas
                $stmt_votaciones = $pdo->prepare("SELECT id_votacion, titulo, descripcion, fecha_inicio, fecha_fin FROM votacion WHERE fecha_inicio <= :now AND fecha_fin >= :now ORDER BY fecha_inicio DESC");
                $stmt_votaciones->bindParam(':now', $now);
                $stmt_votaciones->execute();
                $votaciones = $stmt_votaciones->fetchAll(PDO::FETCH_ASSOC);

                if (count($votaciones) > 0) {
                    foreach ($votaciones as $votacion) {
                        echo '<div class="votacion-card">';
                        echo '<h3>' . htmlspecialchars($votacion['titulo']) . '</h3>';
                        if (!empty($votacion['descripcion'])) {
                            echo '<p>' . htmlspecialchars($votacion['descripcion']) . '</p>';
                        }
                        echo '<p class="fechas">Inicia: ' . date('d/m/Y H:i', strtotime($votacion['fecha_inicio'])) . '</p>';
                        echo '<p class="fechas">Finaliza: ' . date('d/m/Y H:i', strtotime($votacion['fecha_fin'])) . '</p>';

                        // Obtener las opciones para esta votación
                        $stmt_opciones = $pdo->prepare("SELECT id_opcion, texto_opcion FROM opciones_votacion WHERE votacion_id = :votacion_id");
                        $stmt_opciones->bindParam(':votacion_id', $votacion['id_votacion'], PDO::PARAM_INT);
                        $stmt_opciones->execute();
                        $opciones = $stmt_opciones->fetchAll(PDO::FETCH_ASSOC);

                        if (count($opciones) > 0) {
                            echo '<ul class="opciones-list">';
                            foreach ($opciones as $opcion) {
                                echo '<li>' . htmlspecialchars($opcion['texto_opcion']) . '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>No hay opciones para esta votación.</p>';
                        }

                        // --- NUEVA DISPOSICIÓN DE BOTONES ---
                        echo '<div class="votacion-footer">'; // Contenedor Flex para los botones

                            
                            echo '<div class="votacion-left-button">';
                                
                                echo '<a href="' . $basePath . 'web/src/votacion/votar.php?votacion_id=' . htmlspecialchars($votacion['id_votacion']) . '" class="btn-votar">Votar</a>';
                            echo '</div>'; // Cierra votacion-left-button

                            // Botón de Eliminar (solo para administradores)
                            if ($es_administrador) {
                                echo '<div class="votacion-right-button">';
                                    echo '<form action="' . $basePath . 'backend/src/votacion/procesar_eliminacion_votacion.php" method="post" onsubmit="return confirm(\'¿Estás seguro de que quieres eliminar esta votación? Esto eliminará también todos los votos y opciones asociados.\');">';
                                    echo '<input type="hidden" name="id_votacion_a_eliminar" value="' . htmlspecialchars($votacion['id_votacion']) . '">';
                                    echo '<button type="submit" class="btn-eliminar">Eliminar</button>';
                                    echo '</form>';
                                echo '</div>'; 
                            }
                        echo '</div>'; 
                       
                        
                        echo '</div>'; // Cierre de votacion-card
                    }
                } else {
                    echo '<p>No hay votaciones activas en este momento.</p>';
                }

            } catch (PDOException $e) {
                echo '<p class="mensaje-error">Error al cargar las votaciones: ' . htmlspecialchars($e->getMessage()) . '</p>';
                error_log("Error al cargar votaciones: " . $e->getMessage());
            }
            ?>
        </div>
    </main>

    <footer>
        <iframe src="../footer/FOOTER.html" frameborder="0" width="100%" height="300px"></iframe>
    </footer>
</body>
</html>