function convertirABoletin(id) {
    console.log('Iniciando conversión a boletín...', { id_evento: id });

    $.ajax({
        url: '../../../controllers/balneario/eventos/obtener.php',
        method: 'GET',
        data: { id_evento: id },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del servidor:', response);

            if (response.success) {
                const evento = response.data;
                console.log('Datos del evento:', evento);

                const fechaInicioObj = new Date(evento.fecha_inicio_evento);
                const fechaFinObj = new Date(evento.fecha_fin_evento);
                const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                
                const fechaInicioFormateada = fechaInicioObj.toLocaleDateString('es-ES', opciones);
                const fechaFinFormateada = fechaFinObj.toLocaleDateString('es-ES', opciones);
                
                const contenido = `${evento.descripcion_evento}\n\nFecha del evento: Del ${fechaInicioFormateada} hasta ${fechaFinFormateada}`;
                
                console.log('Contenido generado:', {
                    fechaInicio: fechaInicioFormateada,
                    fechaFin: fechaFinFormateada,
                    contenido: contenido
                });

                document.getElementById('previewContenido').innerHTML = `
                    <strong>${evento.titulo_evento}</strong><br><br>
                    ${contenido.replace(/\n/g, '<br>')}
                `;

                const modal = new bootstrap.Modal(document.getElementById('modalConfirmarConversion'));
                modal.show();

                // Almacenar datos para el botón de confirmación
                window.eventoActual = {
                    titulo_boletin: evento.titulo_evento,
                    contenido_boletin: contenido
                };
            } else {
                console.error('Error en la respuesta:', response.message);
                toastr.error(response.message || 'Error al obtener los datos del evento');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la petición AJAX:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            toastr.error('Error al procesar la solicitud');
        }
    });
}

// Manejar el clic en el botón de confirmación
document.getElementById('btnConfirmarConversion').addEventListener('click', function() {
    console.log('Iniciando proceso de conversión...');

    if (!window.eventoActual) {
        console.error('No hay datos del evento para convertir');
        toastr.error('Error: No hay datos del evento para convertir');
        return;
    }

    const btn = $(this);
    const btnText = btn.html();
    btn.prop('disabled', true)
       .html('<i class="bi bi-hourglass-split me-2"></i>Procesando...');

    console.log('Datos a enviar:', window.eventoActual);

    $.ajax({
        url: '../../../controllers/balneario/boletines/convertir_evento.php',
        method: 'POST',
        data: {
            titulo_boletin: window.eventoActual.titulo_boletin,
            contenido_boletin: window.eventoActual.contenido_boletin,
            id_usuario: window.userInfo.usuario_id
        },
        dataType: 'json'
    })
    .done(function(response) {
        console.log('Respuesta del servidor:', response);
        if (response.success) {
            // Cerrar el modal primero
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmarConversion')).hide();
            
            // Mostrar mensaje de éxito
            toastr.success(response.message || 'Boletín creado exitosamente');
            
            // Redireccionar después de un breve delay
            setTimeout(() => {
                console.log('Redirigiendo a lista de boletines...');
                window.location.href = '../boletines/lista.php';
            }, 1500);
        } else {
            console.error('Error en la conversión:', response.message);
            toastr.error(response.message || 'Error al convertir el evento');
            btn.prop('disabled', false).html(btnText);
        }
    })
    .fail(function(xhr, status, error) {
        console.error('Error en la petición AJAX:', {
            status: status,
            error: error,
            response: xhr.responseText
        });
        
        let errorMessage = 'Error al procesar la solicitud';
        try {
            const response = JSON.parse(xhr.responseText);
            if (response && response.message) {
                errorMessage = response.message;
            }
        } catch (e) {
            console.error('Error al parsear respuesta:', e);
        }
        
        toastr.error(errorMessage);
        btn.prop('disabled', false).html(btnText);
    });
}); 