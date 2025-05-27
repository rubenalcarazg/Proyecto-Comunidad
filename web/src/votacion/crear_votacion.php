<?php
session_start();
$basePath = '/Proyecto-Comunidad/';
// ... el resto de tu código ...
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Votación</title>
    <link rel="stylesheet" href="<?= $basePath ?>web/src/home/home.css">
    <link rel="stylesheet" href="<?= $basePath ?>web/src/foro/foro.css">
    <link rel="stylesheet" href="<?= $basePath ?>web/src/votacion/crear_votacion.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
</head>
<body>
    <header>
        <?php include __DIR__ . '/../header/cabecera.php'; ?>
    </header>

    <main id="crear-votacion-content">
        <h2>Crear Nueva Votación</h2>
        <div id="formulario-crear-votacion">
            <form action="../../../backend/src/votacion/procesar_creacion_votacion.php" method="post">
                <div class="form-group">
                    <label for="titulo">Título de la Votación:</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción (opcional):</label>
                    <textarea id="descripcion" name="descripcion"></textarea>
                </div>

                <div class="form-group">
                    <label>Opciones de Voto:</label>
                    <div id="opciones-container">
                        <div class="opcion-input">
                            <input type="text" name="opciones[]" placeholder="Opción 1" required>
                            <button type="button" class="eliminar-opcion">Eliminar</button>
                        </div>
                        <div class="opcion-input">
                            <input type="text" name="opciones[]" placeholder="Opción 2" required>
                            <button type="button" class="eliminar-opcion">Eliminar</button>
                        </div>
                    </div>
                    <button type="button" id="añadir-opcion">Añadir Opción</button>
                </div>

                <div class="form-group">
                    <label for="fecha_inicio">Fecha de Inicio:</label>
                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required>
                </div>

                <div class="form-group">
                    <label for="fecha_fin">Fecha de Fin:</label>
                    <input type="datetime-local" id="fecha_fin" name="fecha_fin" required>
                </div>

                <button type="submit" class="boton-crear-votacion">Crear Votación</button>
            </form>
        </div>
    </main>
    <footer>
        <iframe src="../footer/FOOTER.html" frameborder="0" width="100%" height="300px"></iframe>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const opcionesContainer = document.getElementById('opciones-container');
            const añadirOpcionBtn = document.getElementById('añadir-opcion');
            const maxOpciones = 3; // Definimos el límite máximo de opciones

            añadirOpcionBtn.addEventListener('click', function() {
                if (opcionesContainer.children.length < maxOpciones) {
                    const nuevoInputDiv = document.createElement('div');
                    nuevoInputDiv.classList.add('opcion-input');
                    const nuevoInput = document.createElement('input');
                    nuevoInput.type = 'text';
                    nuevoInput.name = 'opciones[]';
                    nuevoInput.placeholder = `Opción ${opcionesContainer.children.length + 1}`;
                    nuevoInput.required = true;
                    const eliminarBtn = document.createElement('button');
                    eliminarBtn.type = 'button';
                    eliminarBtn.classList.add('eliminar-opcion');
                    eliminarBtn.textContent = 'Eliminar';
                    eliminarBtn.addEventListener('click', function() {
                        opcionesContainer.removeChild(nuevoInputDiv);
                        // Habilitar el botón de añadir si se elimina una opción y estamos por debajo del límite
                        if (opcionesContainer.children.length < maxOpciones) {
                            añadirOpcionBtn.disabled = false;
                        }
                    });
                    nuevoInputDiv.appendChild(nuevoInput);
                    nuevoInputDiv.appendChild(eliminarBtn);
                    opcionesContainer.appendChild(nuevoInputDiv);

                    // Deshabilitar el botón de añadir si alcanzamos el límite
                    if (opcionesContainer.children.length >= maxOpciones) {
                        añadirOpcionBtn.disabled = true;
                    }
                } else {
                    // Opcional: Mostrar un mensaje al usuario indicando que se ha alcanzado el límite
                    alert(`No se pueden añadir más de ${maxOpciones} opciones de voto.`);
                }
            });

            opcionesContainer.addEventListener('click', function(event) {
                if (event.target.classList.contains('eliminar-opcion')) {
                    opcionesContainer.removeChild(event.target.parentNode);
                    // Habilitar el botón de añadir si se elimina una opción y estamos por debajo del límite
                    if (opcionesContainer.children.length < maxOpciones) {
                        añadirOpcionBtn.disabled = false;
                    }
                }
            });

            // Deshabilitar el botón de añadir inicialmente si ya hay el máximo de opciones (por si se renderizan inicialmente)
            if (opcionesContainer.children.length >= maxOpciones) {
                añadirOpcionBtn.disabled = true;
            }
        });
    </script>
</body>
</html>