<?php
session_start(); // Inicia la sesión al principio para acceder a $_SESSION



require_once __DIR__ . '/../conexion_BBDD/conexion_db_pm.php';


// Verifica si la solicitud es de tipo POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $opciones = $_POST['opciones'] ?? []; // 
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';

   
    $id_usuario = 1; 

  
    if (empty($titulo) || count($opciones) < 2 || empty($fecha_inicio) || empty($fecha_fin) || $id_usuario === null) {
        // Redirige con un mensaje de error si faltan datos
        header('Location: ../../../web/src/votacion/crear_votacion.php?error=campos_incompletos');
        exit();
    }

    // Filtra y asegura que las opciones no estén vacías y sean únicas
    $opciones_filtradas = array_filter(array_map('trim', $opciones), 'strlen');
    $opciones_unicas = array_unique($opciones_filtradas);

    if (count($opciones_unicas) < 2) {
        header('Location: ../../../web/src/votacion/crear_votacion.php?error=minimo_dos_opciones');
        exit();
    }
    
    // Validar que la fecha de fin no sea anterior o igual a la de inicio
    if (strtotime($fecha_fin) <= strtotime(date('Y-m-d H:i:s'))) {
        header('Location: ../../../web/src/votacion/crear_votacion.php?error=fecha_inicio_pasada');
        exit();
    }

    if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
        header('Location: ../../../web/src/votacion/crear_votacion.php?error=fecha_fin_invalida');
        exit();
    }

    try {
        
        $pdo->beginTransaction();

        
        $stmt_votacion = $pdo->prepare(
            "INSERT INTO votacion (titulo, descripcion, fecha_inicio, fecha_fin, id_usuario) 
             VALUES (?, ?, ?, ?, ?)"
        );
        // Ejecutar la consulta con los datos
        $stmt_votacion->execute([$titulo, $descripcion, $fecha_inicio, $fecha_fin, $id_usuario]);

        // Obtener el ID de la votación recién insertada, necesario para las opciones
        $votacion_id = $pdo->lastInsertId();

        // Insertar cada opción de voto en la tabla `opciones_votacion`
        $stmt_opcion = $pdo->prepare(
            "INSERT INTO opciones_votacion (votacion_id, texto_opcion) 
             VALUES (?, ?)"
        );
        
        foreach ($opciones_unicas as $opcion_texto) {
            // Asegurarse de que la opción no esté vacía después de trim() y unique()
            if (!empty($opcion_texto)) { 
                $stmt_opcion->execute([$votacion_id, $opcion_texto]);
            }
        }

       
        $pdo->commit();

       
        header("Location: ../../../web/src/votacion/ver_votacion.php?success=votacion_creada");
        exit();

    } catch (PDOException $e) {
        
        $pdo->rollBack();
        
        error_log("Error al crear la votación: " . $e->getMessage());
        header("Location: ../../../web/src/votacion/crear_votacion.php?error=db_error");
        exit();
    }
} else {
    
    header("Location: ../../../web/src/votacion/ver_votacion.php");
    exit();
}
?>