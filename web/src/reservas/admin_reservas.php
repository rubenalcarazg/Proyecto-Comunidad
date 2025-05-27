<?php
session_start();

$rolPermitido = in_array($_SESSION["nombre_rol"] ?? '', ["Admin", "Presidente"]);

if (!$rolPermitido) {
    header("Location: ../eventos/acceso_denegado.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reservas Activas de la Comunidad</title>
    <link rel="stylesheet" href="admin_reservas.css">

</head>

<body class="fondo-cuerpo">


    <main class="contenedor-principal">
        <div class="titulo-con-boton">
            <h2 class="titulo-eventos">Reservas Activas de la Comunidad</h2>
            <a href="reservas.php" class="boton-evento boton-volver">Volver a Reservas</a>
        </div>
        <div id="contenedor-reservas">Cargando reservas...</div>
    </main>


    <script>
        document.addEventListener("DOMContentLoaded", () => {
            fetch("/Proyecto-Comunidad/backend/src/reservas/admin_reservas_logic.php")
                .then(res => res.json())
                .then(data => {
                    const contenedor = document.getElementById("contenedor-reservas");

                    if (data.error) {
                        contenedor.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        return;
                    }

                    if (data.length === 0) {
                        contenedor.innerHTML = `
                            <div class="sin-reservas-wrapper">
                                 <p class="sin-reservas">No hay reservas activas.</p>
                            </div>
                        `;
                        return;
                    }

                    // Agrupar reservas por zona
                    const zonasAgrupadas = {};

                    data.forEach(reserva => {
                        const zona = reserva.zona;

                        if (!zonasAgrupadas[zona]) {
                            zonasAgrupadas[zona] = [];
                        }

                        zonasAgrupadas[zona].push(reserva);
                    });

                    // Generar HTML
                    let htmlFinal = '';

                    for (const [zona, reservas] of Object.entries(zonasAgrupadas)) {
                        htmlFinal += `<h3 style="margin-top: 30px; text-align: center;">Zona: ${zona}</h3>`;
                        htmlFinal += `<table>
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Fecha y Hora</th>
                            </tr>
                        </thead>
                        <tbody>`;

                        reservas.forEach(reserva => {
                            htmlFinal += `<tr>
                            <td>${reserva.nombre_usuario}</td>
                            <td>${new Date(reserva.fecha_reserva).toLocaleString()}</td>
                        </tr>`;
                        });

                        htmlFinal += `</tbody></table>`;
                    }

                    contenedor.innerHTML = htmlFinal;
                })
                .catch(err => {
                    console.error("❌ Error al hacer fetch:", err);
                    document.getElementById("contenedor-reservas").innerHTML = `<p style="color:red;">Error al cargar las reservas.</p>`;
                });
        });
    </script>

</body>

</html>