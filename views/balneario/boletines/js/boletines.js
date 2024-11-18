// Variable global para almacenar los datos del boletín actual
let boletinActual = null;

// Manejar el envío del formulario de boletín
$(document).on('submit', '#formBoletin', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const btnText = submitBtn.html();
    
    // Debug
    console.log('Iniciando envío del formulario');
    console.log('Datos del formulario:', form.serialize());
    
    // Validar campos
    const titulo = form.find('input[name="titulo"]').val().trim();
    const contenido = form.find('textarea[name="contenido"]').val().trim();
    
    if (!titulo || !contenido) {
        toastr.error('Por favor completa todos los campos');
        return;
    }
    
    // Deshabilitar el botón y mostrar indicador de carga
    submitBtn.prop('disabled', true)
           .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');
    
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        dataType: 'json'
    })
    .done(function(response) {
        console.log('Respuesta del servidor:', response);
        
        if (response.success) {
            toastr.success(response.message);
            $('#modalBoletin').modal('hide');
            
            // Recargar la página después de un breve delay
            setTimeout(() => window.location.reload(), 1500);
        } else {
            toastr.error(response.message || 'Error al guardar el boletín');
        }
    })
    .fail(function(xhr, status, error) {
        console.error('Error en la petición:', {xhr, status, error});
        let errorMessage = 'Error al procesar la solicitud';
        
        // Intentar obtener mensaje de error del servidor
        try {
            const response = JSON.parse(xhr.responseText);
            errorMessage = response.message || errorMessage;
        } catch (e) {
            console.error('Error al parsear respuesta:', e);
        }
        
        toastr.error(errorMessage);
    })
    .always(function() {
        // Restaurar el botón
        submitBtn.prop('disabled', false).html(btnText);
    });
});

// Limpiar el formulario cuando se cierre el modal
$('#modalBoletin').on('hidden.bs.modal', function() {
    $('#formBoletin').trigger('reset');
});

// Configuración global de toastr
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 3000
}; 

// Función para mostrar el modal de envío
function enviarBoletin(idBoletin) {
    console.log('Preparando envío de boletín:', idBoletin);
    
    // Obtener datos del boletín
    $.get('../../../controllers/balneario/boletines/obtener.php', {
        id_boletin: idBoletin
    })
    .done(function(response) {
        console.log('Respuesta del servidor:', response);
        
        if (response.success) {
            const boletin = response.data;
            
            // Actualizar modal con los datos del boletín
            $('#id_boletin_envio').val(idBoletin);
            $('#previewTitulo').text(boletin.titulo_boletin);
            $('#previewContenido').html(boletin.contenido_boletin.replace(/\n/g, '<br>'));
            
            // Mostrar modal
            new bootstrap.Modal(document.getElementById('modalEnvioBoletin')).show();
        } else {
            toastr.error(response.message || 'Error al cargar el boletín');
        }
    })
    .fail(function(xhr) {
        console.error('Error en la petición:', xhr);
        toastr.error('Error al cargar el boletín');
    });
}

// Manejar el envío del boletín
$(document).on('click', '#btnConfirmarEnvio', function() {
    const btn = $(this);
    const btnText = btn.html();
    const idBoletin = $('#id_boletin_envio').val();

    if (!idBoletin) {
        toastr.error('Error: No se ha seleccionado un boletín');
        return;
    }

    // Deshabilitar botón y mostrar indicador de carga
    btn.prop('disabled', true)
       .html('<i class="bi bi-hourglass-split me-2"></i>Enviando...');

    $.ajax({
        url: '../../../controllers/balneario/boletines/enviar.php',
        method: 'POST',
        data: { id_boletin: idBoletin },
        dataType: 'json'
    })
    .done(function(response) {
        console.log('Respuesta del servidor:', response);
        
        if (response.success) {
            // Construir mensaje de éxito
            const enviados = parseInt(response.enviados) || 0;
            const totalSuscriptores = parseInt(response.total_suscriptores) || 0;
            
            let mensaje = `${response.message}<br><br>`;
            mensaje += `<strong>Resumen del envío:</strong><br>`;
            mensaje += `Correos enviados exitosamente: ${enviados} de ${totalSuscriptores}`;
            
            // Agregar información de errores si existen
            if (response.errores && Array.isArray(response.errores) && response.errores.length > 0) {
                mensaje += '<br><br><strong>Detalles de errores:</strong>';
                response.errores.forEach(error => {
                    if (error.email && error.error) {
                        mensaje += `<br>• ${error.email}: ${error.error}`;
                    }
                });
            }
            
            toastr.success(mensaje, 'Envío Completado', {
                timeOut: 5000,
                extendedTimeOut: 2000,
                progressBar: true,
                escapeHtml: false,
                closeButton: true
            });
            
            // Recargar la página después de un breve delay
            setTimeout(() => window.location.reload(), 5000);
        } else {
            toastr.error(response.message || 'Error al enviar el boletín');
        }
    })
    .fail(function(xhr) {
        console.error('Error en la petición:', xhr);
        let errorMessage = 'Error al procesar la solicitud';
        try {
            const response = JSON.parse(xhr.responseText);
            errorMessage = response.message || errorMessage;
        } catch (e) {
            console.error('Error al parsear respuesta:', e);
        }
        toastr.error(errorMessage);
    })
    .always(function() {
        // Restaurar botón y cerrar modal
        btn.prop('disabled', false).html(btnText);
        bootstrap.Modal.getInstance(document.getElementById('modalEnvioBoletin')).hide();
    });
}); 