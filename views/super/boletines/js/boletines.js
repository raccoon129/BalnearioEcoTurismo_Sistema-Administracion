// Variable global para almacenar los destinatarios
let destinatariosSeleccionados = {
    superadmin: [],
    admin: [],
    suscriptores: []
};

// Función para mostrar el modal de envío
function mostrarModalEnvio(idBoletin) {
    // Limpiar checkboxes
    $('input[name="destinatarios[]"]').prop('checked', false);
    
    $.get('../../../controllers/super/boletines/obtener.php', {
        id_boletin: idBoletin
    })
    .done(function(response) {
        if (response.success) {
            const boletin = response.data;
            $('#id_boletin_envio').val(idBoletin);
            $('#previewTitulo').text(boletin.titulo_boletin);
            $('#previewContenido').html(boletin.contenido_boletin.replace(/\n/g, '<br>'));
            $('#modalEnvioBoletin').modal('show');
        } else {
            toastr.error('Error al cargar el boletín');
        }
    })
    .fail(function() {
        toastr.error('Error al cargar el boletín');
    });
}

// Manejar cambios en los checkboxes de destinatarios
$(document).on('change', 'input[name="destinatarios[]"]', function() {
    const tipo = $(this).val();
    const checked = $(this).prop('checked');
    
    if (checked) {
        // Solo consultar si no tenemos los destinatarios almacenados
        if (destinatariosSeleccionados[tipo].length === 0) {
            $.ajax({
                url: '../../../controllers/super/boletines/obtener_destinatarios.php',
                method: 'GET',
                data: { tipo: tipo },
                success: function(response) {
                    if (response.success) {
                        destinatariosSeleccionados[tipo] = response.destinatarios;
                        console.group(`Destinatarios tipo: ${tipo}`);
                        console.log(`Total destinatarios: ${response.destinatarios.length}`);
                        response.destinatarios.forEach(dest => {
                            console.log(`${dest.nombre_usuario} (${dest.email_usuario})`);
                        });
                        console.groupEnd();
                    } else {
                        console.error(`Error al obtener destinatarios: ${response.message}`);
                        toastr.error(`Error al obtener destinatarios: ${response.message}`);
                    }
                },
                error: function(xhr) {
                    console.error('Error en la consulta de destinatarios:', xhr.responseText);
                    toastr.error('Error al obtener los destinatarios');
                }
            });
        }
    }
});

// Manejar el envío del boletín
$(document).on('click', '#btnConfirmarEnvio', function() {
    const tiposSeleccionados = [];
    let todosDestinatarios = [];
    
    $('input[name="destinatarios[]"]:checked').each(function() {
        const tipo = $(this).val();
        tiposSeleccionados.push(tipo);
        todosDestinatarios = todosDestinatarios.concat(destinatariosSeleccionados[tipo]);
    });

    if (tiposSeleccionados.length === 0) {
        toastr.error('Debe seleccionar al menos un tipo de destinatario');
        return;
    }

    if (todosDestinatarios.length === 0) {
        toastr.error('No hay destinatarios disponibles para el envío');
        return;
    }

    // Deshabilitar el botón mientras se procesa
    const $btn = $(this);
    $btn.prop('disabled', true)
        .html('<i class="bi bi-send me-2"></i>Enviando...');

    // Realizar el envío
    $.ajax({
        url: '../../../controllers/super/boletines/enviar_boletin_superadmin.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            id_boletin: $('#id_boletin_envio').val(),
            destinatarios: todosDestinatarios
        }),
        success: function(response) {
            if (response.success) {
                let mensaje = `${response.message}<br>`;
                mensaje += `Enviados: ${response.detalles.enviados} de ${response.detalles.total_destinatarios}`;
                
                if (response.detalles.errores && response.detalles.errores.length > 0) {
                    mensaje += '<br>Algunos correos no pudieron ser enviados:';
                    response.detalles.errores.forEach(error => {
                        mensaje += `<br>- ${error.email}: ${error.error}`;
                    });
                }
                
                toastr.success(mensaje, null, {
                    timeOut: 5000,
                    extendedTimeOut: 2000,
                    progressBar: true,
                    escapeHtml: false
                });
                
                $('#modalEnvioBoletin').modal('hide');
                setTimeout(() => {
                    window.location.reload();
                }, 5000);
            } else {
                let errorMsg = response.message;
                if (response.debug_info) {
                    console.error('Debug info:', response.debug_info);
                    errorMsg += '<br>Consulte la consola para más detalles.';
                }
                toastr.error(errorMsg, null, {
                    timeOut: 0,
                    extendedTimeOut: 0,
                    closeButton: true,
                    escapeHtml: false
                });
            }
        },
        error: function(xhr) {
            console.error('Error en la petición:', xhr);
            let errorMessage = 'Error al enviar el boletín';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
                if (response.debug_info) {
                    console.error('Debug info:', response.debug_info);
                    errorMessage += '<br>Consulte la consola para más detalles.';
                }
            } catch (e) {
                console.error('Error parsing response:', xhr.responseText);
                errorMessage += '<br>Error en el formato de respuesta del servidor';
            }
            toastr.error(errorMessage, null, {
                timeOut: 0,
                extendedTimeOut: 0,
                closeButton: true,
                escapeHtml: false
            });
        },
        complete: function() {
            $btn.prop('disabled', false)
                .html('<i class="bi bi-send me-2"></i>Confirmar Envío');
        }
    });
});

// Manejar guardado como borrador
$('#btnGuardarBorrador').on('click', function() {
    const $btn = $(this);
    const formData = new FormData($('#formEditarBoletin')[0]);
    formData.append('accion', 'borrador');

    // Validar campos requeridos
    if (!$('input[name="titulo_boletin"]').val().trim()) {
        toastr.error('El título es requerido');
        return;
    }
    if (!$('textarea[name="contenido_boletin"]').val().trim()) {
        toastr.error('El contenido es requerido');
        return;
    }

    // Log de datos que se enviarán
    console.group('Datos del formulario');
    console.log('ID Boletín:', formData.get('id_boletin'));
    console.log('ID Balneario:', formData.get('id_balneario'));
    console.log('Título:', formData.get('titulo_boletin'));
    console.log('Contenido:', formData.get('contenido_boletin'));
    console.groupEnd();

    // Deshabilitar botón mientras se procesa
    $btn.prop('disabled', true)
        .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

    $.ajax({
        url: '../../../controllers/super/boletines/actualizar.php',
        method: 'POST',
        data: {
            id_boletin: formData.get('id_boletin'),
            id_balneario: formData.get('id_balneario'),
            titulo_boletin: formData.get('titulo_boletin'),
            contenido_boletin: formData.get('contenido_boletin'),
            accion: 'borrador'
        },
        dataType: 'json',
        success: function(response) {
            console.group('Respuesta del servidor - Éxito');
            console.log('Respuesta completa:', response);
            console.groupEnd();

            if (response.success) {
                toastr.success('Boletín guardado como borrador');
                setTimeout(() => {
                    window.location.href = 'balneario.php?id=' + formData.get('id_balneario');
                }, 1500);
            } else {
                console.error('Error en la respuesta:', response.message);
                toastr.error(response.message || 'Error al guardar el boletín');
            }
        },
        error: function(xhr, status, error) {
            console.group('Error en la petición');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Respuesta del servidor:', xhr.responseText);
            console.error('Estado de la petición:', xhr.status);
            console.error('Texto del estado:', xhr.statusText);
            console.groupEnd();

            let errorMessage = 'Error al procesar la solicitud';
            try {
                const response = JSON.parse(xhr.responseText);
                console.log('Respuesta parseada:', response);
                if (response.debug_info) {
                    console.group('Información de depuración');
                    console.log('Archivo:', response.debug_info.file);
                    console.log('Línea:', response.debug_info.line);
                    console.log('Trace:', response.debug_info.trace);
                    console.groupEnd();
                }
                errorMessage = response.message || errorMessage;
            } catch (e) {
                console.error('Error al parsear la respuesta:', e);
                console.log('Respuesta raw:', xhr.responseText);
            }

            toastr.error(errorMessage, 'Error', {
                timeOut: 0,
                extendedTimeOut: 0,
                closeButton: true,
                onclick: function() {
                    console.log('Ver consola para más detalles');
                }
            });
        },
        complete: function() {
            $btn.prop('disabled', false)
                .html('<i class="bi bi-save me-2"></i>Guardar como Borrador');
        }
    });
}); 