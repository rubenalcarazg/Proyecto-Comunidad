<?php
session_start();

$basePath = '/Proyecto-Comunidad/';

// **Ruta de conexión corregida:** Asegúrate de que esta ruta es la correcta.
// Si procesar_voto.php está en 'backend/src/votacion/'
// y conexion_db_pm.php está en 'backend/src/conexion_BBDD/'
// entonces necesitamos subir dos niveles (..) para llegar a 'backend/'
// y luego bajar a 'conexion_BBDD/'
require_once __DIR__ . '/../conexion_BBDD/conexion_db_pm.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . $basePath . "web/src/login/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_votacion"]) && isset($_POST['id_opcion_votada'])) {

    $id_usuario = $_SESSION['id_usuario'];
    $id_votacion = $_POST['id_votacion'];
    $id_opcion_votada = $_POST['id_opcion_votada'];

    if (!is_numeric($id_votacion) || !is_numeric($id_opcion_votada)) {
        header("Location: " . $basePath . "web/src/votacion/votar.php?votacion_id={$id_votacion}&error_voto=datos_invalidos");
        exit();
    }

    try {
        // 1. Verificar si el usuario ya ha votado en ESTA votación
        $stmt_verificar = $pdo->prepare("SELECT COUNT(*) FROM voto WHERE id_usuario = ? AND id_votacion = ?");
        $stmt_verificar->execute([$id_usuario, $id_votacion]);

        if ($stmt_verificar->fetchColumn() > 0) {
            header("Location: " . $basePath . "web/src/votacion/votar.php?votacion_id={$id_votacion}&error_voto=ya_votaste");
            exit();
        }

        // 2. Insertar el voto en la tabla 'voto'
        $sql_insertar = "INSERT INTO voto (id_usuario, id_votacion, id_opcion_votada, fecha_voto) VALUES (?, ?, ?, NOW())";
        $stmt_insertar = $pdo->prepare($sql_insertar);

        if ($stmt_insertar->execute([$id_usuario, $id_votacion, $id_opcion_votada])) {
            // Voto registrado exitosamente.
            // Redirige a la nueva página de resultados, pasándole el ID de la votación
            header("Location: " . $basePath . "web/src/votacion/resultados_votacion.php?votacion_id={$id_votacion}");
            exit();
        } else {
            $errorInfo = $stmt_insertar->errorInfo();
            error_log("Error al registrar el voto (execute): " . implode(":", $errorInfo));
            // Si la ejecución falla pero no es una PDOException (raro en PDO), también queremos saber.
            // TEMPORALMENTE para depuración:
            echo "<h1>Error al Ejecutar Consulta (debug)</h1>";
            echo "<p>Ha ocurrido un error al intentar ejecutar la inserción del voto.</p>";
            echo "<p><strong>SQL Error Info:</strong> " . htmlspecialchars(implode(" | ", $errorInfo)) . "</p>";
            die();

            // header("Location: " . $basePath . "web/src/votacion/votar.php?votacion_id={$id_votacion}&error_voto=db_insert_error");
            // exit();
        }

    } catch (PDOException $e) {
        error_log("Error PDO en procesar_voto: " . $e->getMessage());
        
        // **INICIO DE BLOQUE DE DEPURACIÓN TEMPORAL**
        echo "<h1>Error de Base de Datos (Debug)</h1>";
        echo "<p>Ha ocurrido un error al intentar procesar su voto.</p>";
        echo "<p><strong>Mensaje de error:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Código de error SQLSTATE:</strong> " . $e->getCode() . "</p>";
        
        // Esta parte intenta obtener información más específica si la consulta PDO fue preparada
        if (isset($stmt_insertar) && $stmt_insertar->errorCode() !== '00000') {
             $errorInfo = $stmt_insertar->errorInfo();
             echo "<p><strong>Detalles del Error SQL (Error Info):</strong> " . htmlspecialchars(implode(" | ", $errorInfo)) . "</p>";
        } elseif (isset($stmt_verificar) && $stmt_verificar->errorCode() !== '00000') {
             $errorInfo = $stmt_verificar->errorInfo();
             echo "<p><strong>Detalles del Error SQL (Error Info en verificación):</strong> " . htmlspecialchars(implode(" | ", $errorInfo)) . "</p>";
        }


        die(); // Detiene la ejecución para mostrar el error en pantalla
        // **FIN DE BLOQUE DE DEPURACIÓN TEMPORAL**

        // **Comenta o quita la línea de redirección mientras depuras:**
        // header("Location: " . $basePath . "web/src/votacion/votar.php?votacion_id={$id_votacion}&error_voto=db_error");
        // exit();
    }

} else {
    $id_votacion_fallback = $_POST['id_votacion'] ?? 'unknown';
    header("Location: " . $basePath . "web/src/votacion/votar.php?votacion_id={$id_votacion_fallback}&error_voto=no_datos_recibidos");
    exit();
}