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

document.getElementById('btnConfirmarConversion').addEventListener('click', function() {
    console.log('Iniciando proceso de conversión...');

    const evento = window.eventoActual; // Asegúrate de que esta variable esté definida
    console.log('Datos del evento a convertir:', evento);

    if (!evento) {
        console.error('No hay datos del evento para convertir');
        toastr.error('Error: No hay datos del evento para convertir');
        return;
    }

    const fechaInicioObj = new Date(evento.fecha_inicio_evento);
    const fechaFinObj = new Date(evento.fecha_fin_evento);
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    
    const fechaInicioFormateada = fechaInicioObj.toLocaleDateString('es-ES', opciones);
    const fechaFinFormateada = fechaFinObj.toLocaleDateString('es-ES', opciones);
    
    const contenido = `${evento.descripcion_evento}\n\nFecha del evento: Del ${fechaInicioFormateada} hasta ${fechaFinFormateada}`;

    const datos = {
        id_evento: evento.id_evento,
        titulo_boletin: evento.titulo_evento,
        contenido_boletin: contenido
    };

    console.log('Datos a enviar:', datos);

    $.ajax({
        url: '../../../controllers/balneario/boletines/convertir_evento.php',
        method: 'POST',
        data: datos,
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del servidor:', response);
            if (response.success) {
                toastr.success(response.message);
                setTimeout(() => window.location.href = '../boletines/lista.php', 1500);
            } else {
                console.error('Error en la conversión:', response.message);
                toastr.error(response.message || 'Error al convertir el evento');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la petición AJAX:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            toastr.error('Error al procesar la solicitud');
        },
        complete: function() {
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmarConversion')).hide();
        }
    });
}); 