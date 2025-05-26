<?php
session_start(); // Inicia la sesión al principio para acceder a $_SESSION
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$basePath = '/Proyecto-Comunidad/';

require_once __DIR__ . '/../../../backend/src/conexion_BBDD/conexion_db_pm.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . $basePath . "web/src/login/index.php");
    exit();
}
$id_votacion = $_GET['votacion_id'] ?? null;

if ($id_votacion === null || !is_numeric($id_votacion)) {
    header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=votacion_invalida");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$ya_voto = false;
$id_opcion_votada_usuario = null;
$votacion_activa = false;
$votacion_info = null;
$opciones_votacion = [];

try {
    date_default_timezone_set('Europe/Madrid'); // o la que estés usando
    $now = date('Y-m-d H:i:s');
    echo "Fecha actual del servidor: $now<br>";
    $stmt_votacion = $pdo->prepare("SELECT id_votacion, titulo, descripcion, fecha_inicio, fecha_fin FROM votacion WHERE id_votacion = ?");
    $stmt_votacion->execute([$id_votacion]);
    $votacion_info = $stmt_votacion->fetch(PDO::FETCH_ASSOC);

    if ($votacion_info) {
        $fecha_inicio = strtotime($votacion_info['fecha_inicio']);
        $fecha_fin = strtotime($votacion_info['fecha_fin']);
        $ahora = time();

        $votacion_activa = ($ahora >= $fecha_inicio && $ahora <= $fecha_fin);
        $votacion_futura = ($ahora < $fecha_inicio);
        $votacion_finalizada = ($ahora > $fecha_fin);

        // Obtener opciones
        $stmt_opciones = $pdo->prepare("SELECT id_opcion, texto_opcion FROM opciones_votacion WHERE votacion_id = ?");
        $stmt_opciones->execute([$id_votacion]);
        $opciones_votacion = $stmt_opciones->fetchAll(PDO::FETCH_ASSOC);

        // Verificar si el usuario ya ha votado
        $stmt_verificar_voto = $pdo->prepare("SELECT id_opcion_votada FROM voto WHERE id_usuario = ? AND id_votacion = ?");
        $stmt_verificar_voto->execute([$id_usuario, $id_votacion]);
        $resultado_voto = $stmt_verificar_voto->fetch(PDO::FETCH_ASSOC);

        if ($resultado_voto) {
            $ya_voto = true;
            $id_opcion_votada_usuario = $resultado_voto['id_opcion_votada'];
        }

    } else {
        header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=votacion_no_disponible");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error al cargar votación o verificar voto en votar.php: " . $e->getMessage());
    header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=db_error");
    exit();
}
$mensaje_exito = isset($_GET['success_voto']) && $_GET['success_voto'] == 1 ? "¡Tu voto ha sido registrado exitosamente!" : '';
$mensaje_error = isset($_GET['error_voto']) && $_GET['error_voto'] == 'ya_votaste' ? "Ya has votado en esta encuesta. ¡Gracias por tu participación!" : '';
$mensaje_error_datos_invalidos = isset($_GET['error_voto']) && $_GET['error_voto'] == 'datos_invalidos' ? "Error: Los datos de la votación no son válidos." : '';
$mensaje_error_no_recibidos = isset($_GET['error_voto']) && $_GET['error_voto'] == 'no_datos_recibidos' ? "Error: No se recibieron los datos de tu voto. Por favor, inténtalo de nuevo." : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votar: <?= htmlspecialchars($votacion_info['titulo'] ?? 'Votación') ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>web/src/home/home.css">
    <link rel="stylesheet" href="<?= $basePath ?>web/src/foro/foro.css">
    <link rel="stylesheet" href="<?= $basePath ?>web/src/votacion/votar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
</head>

<body>
    <header>
        <?php include __DIR__ . '/../header/cabecera.php'; ?>
    </header>

    <main>
        <div class="votacion-container">
            <?php if ($mensaje_exito): ?>
                <p class="mensaje-exito"><?= $mensaje_exito ?></p>
            <?php elseif ($mensaje_error): ?>
                <p class="mensaje-error"><?= $mensaje_error ?></p>
            <?php elseif ($mensaje_error_datos_invalidos): ?>
                <p class="mensaje-error"><?= $mensaje_error_datos_invalidos ?></p>
            <?php elseif ($mensaje_error_no_recibidos): ?>
                <p class="mensaje-error"><?= $mensaje_error_no_recibidos ?></p>
            <?php endif; ?>

            <?php if ($votacion_info): ?>
                <h1><?= htmlspecialchars($votacion_info['titulo']) ?></h1>
                <?php if (!empty($votacion_info['descripcion'])): ?>
                    <p class="descripcion"><?= htmlspecialchars($votacion_info['descripcion']) ?></p>
                <?php endif; ?>
                <p class="fechas">Inicia: <?= date('d/m/Y H:i', strtotime($votacion_info['fecha_inicio'])) ?></p>
                <p class="fechas">Finaliza: <?= date('d/m/Y H:i', strtotime($votacion_info['fecha_fin'])) ?></p>

                <?php if ($ya_voto): ?>
                    <p class="mensaje-ya-voto">Ya has votado en esta votación. ¡Gracias por tu participación!</p>
                    <div class="opciones-grupo opciones-deshabilitadas">
                        <?php foreach ($opciones_votacion as $opcion): ?>
                            <?php
                                $is_checked = ($opcion['id_opcion'] == $id_opcion_votada_usuario) ? 'checked' : '';
                                $clase_opcion_votada = ($opcion['id_opcion'] == $id_opcion_votada_usuario) ? 'opcion-previamente-votada' : '';
                            ?>
                            <div class="opcion-item <?= $clase_opcion_votada ?>">
                                <input type="radio" id="opcion_<?= $opcion['id_opcion'] ?>" name="opcion_votada_disabled" disabled <?= $is_checked ?>>
                                <label for="opcion_<?= $opcion['id_opcion'] ?>"><?= htmlspecialchars($opcion['texto_opcion']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= $basePath ?>web/src/votacion/resultados_votacion.php?votacion_id=<?= htmlspecialchars($id_votacion) ?>" class="boton-resultados">
                        Ver Resultados
                    </a>
                <?php elseif ($votacion_finalizada): ?>
                    <p class="mensaje-error">Esta votación ha finalizado. Ya no se pueden emitir votos.</p>
                    <a href="<?= $basePath ?>web/src/votacion/resultados_votacion.php?votacion_id=<?= htmlspecialchars($id_votacion) ?>" class="boton-resultados">
                        Ver Resultados
                    </a>
                <?php elseif ($votacion_futura): ?>
                    <p class="mensaje-error">La votación aún no ha comenzado. Vuelve más tarde para participar.</p>
                <?php else: ?>
                    <form action="<?= $basePath ?>backend/src/votacion/procesar_voto.php" method="post">
                        <input type="hidden" name="id_votacion" value="<?= htmlspecialchars($id_votacion) ?>">
                        <div class="opciones-grupo">
                            <?php if (count($opciones_votacion) > 0): ?>
                                <?php foreach ($opciones_votacion as $opcion): ?>
                                    <div class="opcion-item">
                                        <input type="radio" id="opcion_<?= $opcion['id_opcion'] ?>" name="id_opcion_votada" value="<?= htmlspecialchars($opcion['id_opcion']) ?>" required>
                                        <label for="opcion_<?= $opcion['id_opcion'] ?>"><?= htmlspecialchars($opcion['texto_opcion']) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Esta votación no tiene opciones disponibles.</p>
                            <?php endif; ?>
                        </div>
                        <?php if (count($opciones_votacion) > 0): ?>
                            <button type="submit">Votar</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>

            <?php else: ?>
                <p class="mensaje-error">La votación no está disponible o no existe.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <iframe src="../footer/FOOTER.html" frameborder="0" width="100%" height="300px"></iframe>
    </footer>
</body>
</html>