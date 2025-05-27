<?php
session_start();
$basePath = '/Proyecto-Comunidad/';

require_once __DIR__ . '/../../../backend/src/conexion_BBDD/conexion_db_pm.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . $basePath . "web/src/login/index.php");
    exit();
}

$id_votacion = $_GET['votacion_id'] ?? null;

if ($id_votacion === null || !is_numeric($id_votacion)) {
    header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=resultados_invalidos");
    exit();
}

$votacion_info = null;
$resultados_opciones = [];
$total_votos_votacion = 0;

try {
    $stmt_votacion = $pdo->prepare("SELECT id_votacion, titulo, descripcion FROM votacion WHERE id_votacion = ?");
    $stmt_votacion->execute([$id_votacion]);
    $votacion_info = $stmt_votacion->fetch(PDO::FETCH_ASSOC);

    if (!$votacion_info) {
        header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=votacion_no_encontrada");
        exit();
    }

    $stmt_total_votos = $pdo->prepare("SELECT COUNT(*) FROM voto WHERE id_votacion = ?");
    $stmt_total_votos->execute([$id_votacion]);
    $total_votos_votacion = $stmt_total_votos->fetchColumn();

    $sql_resultados = "
        SELECT
            ov.texto_opcion,
            COUNT(v.id_opcion_votada) AS total_votos_opcion
        FROM opciones_votacion ov
        LEFT JOIN voto v ON ov.id_opcion = v.id_opcion_votada AND v.id_votacion = :id_votacion
        WHERE ov.votacion_id = :id_votacion_2
        GROUP BY ov.id_opcion, ov.texto_opcion
        ORDER BY total_votos_opcion DESC, ov.id_opcion ASC
    ";
    $stmt_resultados = $pdo->prepare($sql_resultados);
    $stmt_resultados->bindParam(':id_votacion', $id_votacion, PDO::PARAM_INT);
    $stmt_resultados->bindParam(':id_votacion_2', $id_votacion, PDO::PARAM_INT);
    $stmt_resultados->execute();
    $resultados_opciones = $stmt_resultados->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error al cargar resultados de votación: " . $e->getMessage());
    header("Location: " . $basePath . "web/src/votacion/ver_votacion.php?error=db_error_resultados");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Votación: <?= htmlspecialchars($votacion_info['titulo'] ?? 'Votación') ?></title>

    <link rel="stylesheet" href="<?= $basePath ?>web/src/home/home.css">
    <link rel="stylesheet" href="<?= $basePath ?>web/src/foro/foro.css">
    <link rel="stylesheet" href="<?= $basePath ?>web/src/votacion/votar.css">

    <link rel="stylesheet" href="<?= $basePath ?>web/src/votacion/resultados_votacion.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
</head>

<body>
    <header>
        <?php include __DIR__ . '/../header/cabecera.php'; ?>
    </header>

    <main class="main-content">
        <div class="resultados-container">
            <h1>Resultados de la Votación</h1>
            <p class="titulo-votacion-resultados"><?= htmlspecialchars($votacion_info['titulo']) ?></p>
            <?php if (!empty($votacion_info['descripcion'])): ?>
                <p class="descripcion-votacion-resultados"><?= htmlspecialchars($votacion_info['descripcion']) ?></p>
            <?php endif; ?>

            <img src="<?= $basePath ?>web/etc/assets/img/voto_registrado.png" alt="Icono de voto registrado" class="imagen-agradecimiento">
            <p class="mensaje-agradecimiento">¡Gracias por tu participación!</p>

            <?php if ($total_votos_votacion > 0): ?>
                <p class="total-votos">Total de votos emitidos: <span><?= $total_votos_votacion ?></span></p>
                <div class="barras-resultados">
                    <?php foreach ($resultados_opciones as $resultado): ?>
                        <?php
                            $porcentaje = ($resultado['total_votos_opcion'] / $total_votos_votacion) * 100;
                        ?>
                        <div class="barra-item">
                            <span class="opcion-texto"><?= htmlspecialchars($resultado['texto_opcion']) ?></span>
                            <div class="barra-progreso-exterior">
                                <div class="barra-progreso-interior" style="width: <?= round($porcentaje, 1) ?>%;"></div>
                                <span class="porcentaje-votos"><?= round($porcentaje, 1) ?>% (<?= $resultado['total_votos_opcion'] ?> votos)</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-votos">Aún no hay votos registrados para esta votación.</p>
            <?php endif; ?>

            <a href="<?= $basePath ?>web/src/home/index.php" class="btn-volver-home">Volver a la página principal</a>
        </div>
    </main>

    <footer>
        <?php include __DIR__ . '/../footer/FOOTER.html'; ?>
    </footer>
</body>
</html>