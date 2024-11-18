function convertirABoletin(id) {
    console.log('Iniciando conversión a boletín...', { id_promocion: id });

    $.ajax({
        url: '../../../controllers/balneario/promociones/obtener.php',
        method: 'GET',
        data: { id_promocion: id },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del servidor:', response);

            if (response.success) {
                const promocion = response.data;
                console.log('Datos de la promoción:', promocion);

                const fechaInicioObj = new Date(promocion.fecha_inicio_promocion);
                const fechaFinObj = new Date(promocion.fecha_fin_promocion);
                const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                
                const fechaInicioFormateada = fechaInicioObj.toLocaleDateString('es-ES', opciones);
                const fechaFinFormateada = fechaFinObj.toLocaleDateString('es-ES', opciones);
                
                const contenido = `${promocion.descripcion_promocion}\n\nPromoción válida del ${fechaInicioFormateada} hasta ${fechaFinFormateada}`;
                
                console.log('Contenido generado:', {
                    fechaInicio: fechaInicioFormateada,
                    fechaFin: fechaFinFormateada,
                    contenido: contenido
                });

                // Almacenar datos para el botón de confirmación
                window.promocionActual = {
                    id_promocion: promocion.id_promocion,
                    titulo_boletin: promocion.titulo_promocion,
                    contenido_boletin: contenido
                };

                document.getElementById('previewContenido').innerHTML = `
                    <strong>${promocion.titulo_promocion}</strong><br><br>
                    ${contenido.replace(/\n/g, '<br>')}
                `;

                const modal = new bootstrap.Modal(document.getElementById('modalConfirmarConversion'));
                modal.show();
            } else {
                console.error('Error en la respuesta:', response.message);
                toastr.error(response.message || 'Error al obtener los datos de la promoción');
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

    if (!window.promocionActual) {
        console.error('No hay datos de la promoción para convertir');
        toastr.error('Error: No hay datos de la promoción para convertir');
        return;
    }

    const btn = $(this);
    const btnText = btn.html();
    btn.prop('disabled', true)
       .html('<i class="bi bi-hourglass-split me-2"></i>Procesando...');

    console.log('Datos a enviar:', window.promocionActual);

    $.ajax({
        url: '../../../controllers/balneario/boletines/convertir_promocion.php',
        method: 'POST',
        data: window.promocionActual,
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del servidor:', response);
            if (response.success) {
                toastr.success(response.message);
                setTimeout(() => window.location.href = '../boletines/lista.php', 1500);
            } else {
                console.error('Error en la conversión:', response.message);
                toastr.error(response.message || 'Error al convertir la promoción');
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
            btn.prop('disabled', false).html(btnText);
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmarConversion')).hide();
        }
    });
}); 