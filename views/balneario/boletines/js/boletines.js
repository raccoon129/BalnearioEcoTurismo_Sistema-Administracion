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