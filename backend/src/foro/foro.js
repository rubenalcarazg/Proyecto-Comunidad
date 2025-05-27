document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM completamente cargado y parseado');

    const foroMensajes = document.getElementById('foro-mensajes');
    const enviarHiloBtn = document.getElementById('enviar-hilo');
    const nuevoHiloTituloInput = document.getElementById('nuevo-hilo-titulo');
    const nuevoHiloContenidoTextarea = document.getElementById('nuevo-hilo-contenido');
    const hiloTemplate = document.getElementById('hilo-template');
    const respuestaTemplate = document.getElementById('respuesta-template');
    let hilos = [];

    function mostrarHilos() {
        foroMensajes.innerHTML = '';
        fetch('../../../backend/src/foro/obtener_hilos.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error al obtener los hilos:', data.error);
                    foroMensajes.innerHTML = '<p class="error-message">Error al cargar los hilos del foro.</p>';
                    return;
                }
                hilos = data;
                hilos.forEach(hilo => {
                    const hiloDiv = crearHiloHTML(hilo);
                    foroMensajes.appendChild(hiloDiv);
                });
                establecerEventosHilos();
            })
            .catch(error => {
                console.error('Error de red al obtener los hilos:', error);
                foroMensajes.innerHTML = '<p class="error-message">Error de red al cargar los hilos del foro.</p>';
            });
    }

    function crearHiloHTML(hilo) {
        const hiloDiv = hiloTemplate.content.cloneNode(true);
        hiloDiv.querySelector('.hilo-foro').dataset.id = hilo.id;
        hiloDiv.querySelector('.hilo-titulo').textContent = hilo.titulo;
        hiloDiv.querySelector('.hilo-autor').textContent = hilo.autor;
        hiloDiv.querySelector('.hilo-fecha').textContent = formatDate(hilo.fecha);
        hiloDiv.querySelector('.likes-count').textContent = hilo.likes || 0;
        hiloDiv.querySelector('.dislikes-count').textContent = hilo.dislikes || 0;

        const accionesAdmin = hiloDiv.querySelector('.acciones-admin');
        accionesAdmin.style.display = 'none'; // Ocultar por defecto
        const borrarBtn = accionesAdmin.querySelector('.borrar-hilo');
        if (borrarBtn) {
            borrarBtn.dataset.id = hilo.id;
            accionesAdmin.style.display = 'flex'; // Considerar lógica backend para mostrar condicionalmente
        }
        const bannearBtn = accionesAdmin.querySelector('.bannear-usuario');
        if (bannearBtn) {
            bannearBtn.dataset.autor = hilo.autor;
            accionesAdmin.style.display = 'flex'; // Considerar lógica backend para mostrar condicionalmente
        }
        const timeoutContainer = accionesAdmin.querySelector('.timeout-container');
        if (timeoutContainer) {
            timeoutContainer.dataset.autor = hilo.autor;
            accionesAdmin.style.display = 'flex'; // Considerar lógica backend para mostrar condicionalmente
        }

        const respuestasDiv = hiloDiv.querySelector('.hilo-respuestas');
        hilo.respuestas.forEach(respuesta => {
            const respuestaDiv = respuestaTemplate.content.cloneNode(true);
            respuestaDiv.querySelector('.autor-respuesta').textContent = `${respuesta.autor} respondió el`;
            respuestaDiv.querySelector('.respuesta-fecha').textContent = formatDate(respuesta.fecha);
            respuestaDiv.querySelector('.respuesta-contenido').textContent = respuesta.contenido;
            respuestasDiv.appendChild(respuestaDiv);
        });
        hiloDiv.querySelector('.responder-btn').dataset.id = hilo.id;
        hiloDiv.querySelector('.nuevo-respuesta .enviar-respuesta').dataset.id = hilo.id;
        hiloDiv.querySelector('.nuevo-respuesta .respuesta-texto').id = `respuesta-texto-${hilo.id}`;
        hiloDiv.querySelector('.like-btn').dataset.id = hilo.id;
        hiloDiv.querySelector('.dislike-btn').dataset.id = hilo.id;

        return hiloDiv;
    }

    function establecerEventosHilos() {
        document.querySelectorAll('#foro-mensajes .like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const hiloId = parseInt(this.dataset.id);
                gestionarLikeDislike(hiloId, 'like', 'add');
            });
        });

        document.querySelectorAll('#foro-mensajes .dislike-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const hiloId = parseInt(this.dataset.id);
                gestionarLikeDislike(hiloId, 'dislike', 'add');
            });
        });

        document.querySelectorAll('#foro-mensajes .responder-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const respuestasDiv = this.closest('.hilo-foro').querySelector('.hilo-respuestas');
                respuestasDiv.style.display = respuestasDiv.style.display === 'none' ? 'block' : 'none';
            });
        });

        document.querySelectorAll('#foro-mensajes .enviar-respuesta').forEach(btn => {
            btn.addEventListener('click', function() {
                const hiloId = parseInt(this.dataset.id);
                const respuestaTexto = this.closest('.hilo-foro').querySelector('.respuesta-texto').value;
                if (respuestaTexto.trim()) {
                    enviarRespuesta(hiloId, respuestaTexto);
                    this.closest('.nuevo-respuesta').querySelector('.respuesta-texto').value = '';
                }
            });
        });

        document.querySelectorAll('#foro-mensajes .borrar-hilo').forEach(btn => {
            btn.addEventListener('click', function() {
                const hiloId = parseInt(this.dataset.id);
                borrarHilo(hiloId);
            });
        });

        document.querySelectorAll('#foro-mensajes .bannear-usuario').forEach(btn => {
            btn.addEventListener('click', function() {
                const usuario = this.dataset.autor;
                fetch('../../../backend/src/foro/bannear_usuario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `usuario=${encodeURIComponent(usuario)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.success);
                        mostrarHilos();
                    } else if (data.error) {
                        alert(data.error);
                    } else {
                        alert('Error al intentar bannear al usuario.');
                    }
                })
                .catch(error => {
                    console.error('Error al bannear usuario:', error);
                    alert('Error de comunicación con el servidor al bannear.');
                });
            });
        });

        document.querySelectorAll('#foro-mensajes .timeout-usuario').forEach(btn => {
            btn.addEventListener('click', function() {
                const usuario = this.closest('.acciones-admin').dataset.autor;
                const duracionInput = this.closest('.acciones-admin').querySelector('.timeout-duration');
                const duracion = duracionInput ? duracionInput.value : '';

                if (duracion.trim()) {
                    fetch('../../../backend/src/foro/timeout_usuario.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `usuario=${encodeURIComponent(usuario)}&duracion=${encodeURIComponent(duracion)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.success);
                            mostrarHilos();
                        } else if (data.error) {
                            alert(data.error);
                        } else {
                            alert('Error al aplicar timeout al usuario.');
                        }
                    })
                    .catch(error => {
                        console.error('Error al aplicar timeout:', error);
                        alert('Error de comunicación con el servidor al aplicar timeout.');
                    });
                } else {
                    alert('Por favor, introduce una duración para el timeout (en minutos).');
                }
            });
        });
    }

    enviarHiloBtn.addEventListener('click', function() {
        console.log('Botón de enviar presionado');
        const titulo = nuevoHiloTituloInput.value;
        const contenido = nuevoHiloContenidoTextarea.value;
        if (titulo.trim() && contenido.trim()) {
            fetch('../../../backend/src/foro/crear_hilo.php', {
                method: 'POST', // Asegúrate de que esta línea esté aquí
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `titulo=${encodeURIComponent(titulo)}&contenido=${encodeURIComponent(contenido)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    nuevoHiloTituloInput.value = '';
                    nuevoHiloContenidoTextarea.value = '';
                    mostrarHilos();
                } else if (data.error) {
                    alert(data.error);
                } else {
                    alert('Error al crear el hilo.');
                }
            })
            .catch(error => {
                console.error('Error al crear el hilo:', error);
                alert('Error de comunicación con el servidor al crear el hilo.');
            });
        } else {
            alert('Por favor, introduce un título y un contenido para el hilo.');
        }
    });

    function gestionarLikeDislike(hiloId, accion, tipo) {
        fetch('../../../backend/src/foro/gestionar_like_dislike.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_hilo=${encodeURIComponent(hiloId)}&accion=${encodeURIComponent(accion)}&tipo=${encodeURIComponent(tipo)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.info) {
                mostrarHilos();
            } else if (data.error) {
                alert(data.error);
            } else {
                alert(`Error al ${accion}.`);
            }
        })
        .catch(error => {
            console.error(`Error al ${accion}:`, error);
            alert(`Error de comunicación con el servidor al ${accion}.`);
        });
    }

    function enviarRespuesta(hiloId, contenido) {
        fetch('../../../backend/src/foro/enviar_respuesta.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_hilo=${encodeURIComponent(hiloId)}&contenido=${encodeURIComponent(contenido)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarHilos();
            } else if (data.error) {
                alert(data.error);
            } else {
                alert('Error al enviar la respuesta.');
            }
        })
        .catch(error => {
            console.error('Error al enviar la respuesta:', error);
            alert('Error de comunicación con el servidor al enviar la respuesta.');
        });
    }

    function borrarHilo(hiloId) {
        if (confirm('¿Estás seguro de que quieres borrar este hilo?')) {
            fetch('../../../backend/src/foro/borrar_hilo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_hilo=${encodeURIComponent(hiloId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarHilos();
                } else if (data.error) {
                    alert(data.error);
                } else {
                    alert('Error al borrar el hilo.');
                }
            })
            .catch(error => {
                console.error('Error al borrar el hilo:', error);
                alert('Error de comunicación con el servidor al borrar el hilo.');
            });
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return date.toLocaleDateString('es-ES', options);
    }

    mostrarHilos();
});