<?php
session_start();
// Asegúrate de que $basePath sea correcto para la raíz de tu proyecto web
$basePath = '/Proyecto-Comunidad/'; 

// Incluye el archivo de conexión a la base de datos
require_once __DIR__ . '/../conexion_BBDD/conexion_db_pm.php';

// --- VERIFICACIÓN DE SEGURIDAD Y PERMISOS ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=solicitud_invalida");
    exit();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . $basePath . "web/src/login/index.php"); // Redirige al login si no hay sesión
    exit();
}

if (!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'Admin') {
    header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=permisos_insuficientes");
    exit();
}

if (!isset($_POST['id_votacion_a_eliminar']) || !is_numeric($_POST['id_votacion_a_eliminar'])) {
    header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=id_votacion_invalido");
    exit();
}

$id_votacion = (int)$_POST['id_votacion_a_eliminar'];

try {
    $pdo->beginTransaction(); // Inicia una transacción para asegurar la atomicidad

    
    $stmt_delete_votos = $pdo->prepare("DELETE FROM voto WHERE id_votacion = ?");
    $stmt_delete_votos->execute([$id_votacion]);

    $stmt_delete_votacion = $pdo->prepare("DELETE FROM votacion WHERE id_votacion = ?");
    $stmt_delete_votacion->execute([$id_votacion]);

    $pdo->commit(); 

    
    header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?success=votacion_eliminada");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack(); // Deshace la transacción si algo falló
    error_log("Error al eliminar votación ID $id_votacion: " . $e->getMessage());
    header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=eliminacion_fallida");
    exit();
}
?>