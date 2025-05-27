<?php

// Asegúrate de que la sesión se haya iniciado en los archivos principales que incluyen esta cabecera.
// Si no es el caso, descomenta la siguiente línea.
session_start(); // Es crucial que la sesión esté iniciada para acceder a $_SESSION

$basePath = '/Proyecto-Comunidad/';

// Lógica para verificar si el usuario es administrador
$es_administrador = false;
if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Admin') {
    $es_administrador = true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidad de Vecinos</title>

    <link rel="stylesheet" href="<?= $basePath ?>web/src/header/cabecera.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a class="logo" href="<?= $basePath ?>web/src/home/index.php" target="_top">
                <img src="<?= $basePath ?>assets/img/LOGO_2.png" alt="logo comunidad">
            </a>

            <nav class="nav-container">
                <a href="<?= $basePath ?>web/src/home/index.php" class="nav-link" target="_top">INICIO</a>
                <a href="<?= $basePath ?>web/src/eventos/index.php" class="nav-link" target="_top">EVENTOS</a>
                <a href="<?= $basePath ?>web/src/noticias/index.php" class="nav-link" target="_top">NOTICIAS</a>
                <a href="<?= $basePath ?>web/src/foro/index.php" class="nav-link" target="_top">FORO</a>
                
                <div class="nav-item-votaciones">
                    <a href="<?= $basePath ?>web/src/votacion/ver_votacion.php" class="nav-link">VOTACIONES</a>
                    <?php if ($es_administrador): ?>
                        <div class="votaciones-dropdown-content">
                            <a href="<?= $basePath ?>web/src/votacion/ver_votacion.php">Ver Votaciones Activas</a>
                            <a href="<?= $basePath ?>web/src/votacion/crear_votacion.php">Crear Nueva Votación</a>
                        </div>
                    <?php endif; ?>
                </div>

                <a href="<?= $basePath ?>web/src/reservas/reservas.php" class="nav-link" target="_top">RESERVAS</a>
                <a href="<?= $basePath ?>web/src/contacto/index.php" class="nav-link" target="_top">SOPORTE</a>
            </nav>
            
            <div class="buttons-container">
                <button onclick="navToLogin()" id="boton-login" class="boton-login">Iniciar Sesión</button>
            </div>
            
            <div class="user-info-container" style="display: none;">
                <span id="welcome-message" class="welcome-message"></span>
                <div class="user-dropdown">
                    <button class="user-dropdown-btn">&#9660;</button>
                    <div class="user-dropdown-content">
                        <p id="user-email" class="user-email"></p>
                        <?php if (isset($_SESSION["nombre_rol"]) && $_SESSION["nombre_rol"] === "Admin"): ?>
                            <a href="<?= $basePath ?>web/src/admin_panel/index.php" class="admin-link">Panel de Admin</a>
                        <?php endif; ?>
                        <a href="<?= $basePath ?>backend/src/login/logout.php" id="logout-link" class="logout-link">Cerrar sesión</a>
                </div>
            </div>
        </div>
    
            <!-- Botón hamburguesa dentro del header -->
            <button class="hamburger" id="hamburger">
                <span class="icon-menu">&#9776;     </span>
                <span class="icon-close">&#10005;   </span>
            </button>
            
        </div>

        <!-- Menú desplegable al pulsar hamburguesa -->
        <div class="mobile-menu" id="mobileMenu">
            <nav class="nav-container">
                <a href="<?= $basePath ?>web/src/home/index.php" class="nav-link" target="_top">INICIO</a>
                <a href="<?= $basePath ?>web/src/eventos/index.php" class="nav-link" target="_top">EVENTOS</a>
                <a href="<?= $basePath ?>web/src/noticias/index.php" class="nav-link" target="_top">NOTICIAS</a>
                <a href="<?= $basePath ?>web/src/foro/index.php" class="nav-link" target="_top">FORO</a>
                <a href="#" class="nav-link">VOTACIONES</a>
                <a href="<?= $basePath ?>web/src/reservas/reservas.php" class="nav-link" target="_top">RESERVAS</a>
                <a href="<?= $basePath ?>web/src/contacto/index.php" class="nav-link" target="_top">SOPORTE</a>
            </nav>

            <?php if (!isset($_SESSION['usuario'])): ?>
            <div class="buttons-container">
                <button onclick="navToLogin()" id="boton-login" class="boton-login">Iniciar Sesión</button>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['usuario'])): ?>
    <div class="user-info-container" id="user-info-mobile">
        <div class="user-dropdown">
            <span class="welcome-message">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
            <button class="user-dropdown-btn">&#9660;</button>
            <div class="user-dropdown-content">
                <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Admin'): ?>
                    <a href="<?= $basePath ?>web/src/admin_panel/index.php" class="admin-link">Panel de Admin</a>
                <?php endif; ?>
                <a href="<?= $basePath ?>backend/src/login/logout.php" class="logout-link">Cerrar sesión</a>
            </div>
        </div>
    </div>
<?php endif; ?>
        </div>
    </header>

    <script src="<?= $basePath ?>web/src/header/cabecera.js"></script>
</body>
</html>