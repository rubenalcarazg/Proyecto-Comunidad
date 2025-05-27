<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$zona = isset($_GET['zona']) ? htmlspecialchars($_GET['zona']) : 'Zona desconocida';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar <?php echo $zona; ?></title>
    <link rel="stylesheet" href="reservas_formulario.css">

    <!-- Flatpickr calendario -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
</head>
<body class="fondo-cuerpo">


<main class="formulario-reserva"> 
    <h2>Reservar: <?php echo $zona; ?></h2> <br>

    <form action="../../../backend/src/reservas/procesar_reserva.php" method="POST">
        <input type="hidden" name="zona" value="<?php echo $zona; ?>">
        
        <label for="fecha">Fecha de Reserva:</label>
        <input type="text" name="fecha" id="fecha" required>

        <label for="turno">Turno:</label>
        <select name="turno" id="turno" required>
            <option value="">-- Selecciona --</option>
            <option value="mañana">Mañana</option>
            <option value="tarde">Tarde</option>
        </select>

        <div class="botones-reserva">
    <button class="boton-reserva" type="submit">Reservar</button>
    <a href="/../Proyecto-Comunidad/web/src/reservas/reservas.php" class="boton-reserva boton-volver">Volver a Reservas</a>
</div>

            </form>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const zona = "<?php echo $zona; ?>";

    // Fechas deshabilitadas
    fetch(`../../../backend/src/reservas/obtener_fechas_completas.php?zona=${encodeURIComponent(zona)}`)
        .then(response => response.json())
        .then(fechasCompletas => {
            flatpickr("#fecha", {
                locale: "es",
                minDate: "today",
                dateFormat: "Y-m-d",
                disable: fechasCompletas,
                onChange: actualizarTurnos
            });
        });

    function actualizarTurnos(selectedDates, dateStr, instance) {
        const zona = "<?php echo $zona; ?>";
        const fecha = dateStr;

        if (!fecha) return;

        fetch(`../../../backend/src/reservas/obtener_turnos_disponibles.php?zona=${encodeURIComponent(zona)}&fecha=${fecha}`)
            .then(response => response.json())
            .then(data => {
                const turnoSelect = document.getElementById('turno');
                turnoSelect.innerHTML = '<option value="">-- Selecciona --</option>';

                const opciones = {
                    mañana: 'Mañana',
                    tarde: 'Tarde'
                };

                for (const clave in opciones) {
                    const option = document.createElement('option');
                    option.value = clave;
                    option.textContent = opciones[clave];
                    if (!data.includes(clave)) {
                        option.disabled = true;
                        option.textContent += ' (Completo)';
                    }
                    turnoSelect.appendChild(option);
                }
            });
    }
});
</script>

</body>
</html>
