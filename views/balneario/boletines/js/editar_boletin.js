// Configuración global de toastr
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 3000
};

// Manejar el envío del formulario de edición
$('#formEditarBoletin').on('submit', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const btnText = submitBtn.html();
    
    submitBtn.prop('disabled', true)
           .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');
    
    $.ajax({
        url: '../../../controllers/balneario/boletines/actualizar.php',
        method: 'POST',
        data: form.serialize(),
        dataType: 'json'
    })
    .done(function(response) {
        if (response.success) {
            toastr.success(response.message);
            setTimeout(() => window.location.href = 'lista.php', 1500);
        } else {
            toastr.error(response.message || 'Error al actualizar el boletín');
        }
    })
    .fail(function(xhr) {
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
        submitBtn.prop('disabled', false).html(btnText);
    });
});

// Función para confirmar eliminación
function confirmarEliminacion(idBoletin) {
    if (confirm('¿Está seguro que desea eliminar este boletín? Esta acción no se puede deshacer.')) {
        $.ajax({
            url: '../../../controllers/balneario/boletines/eliminar.php',
            method: 'POST',
            data: { id_boletin: idBoletin },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                setTimeout(() => window.location.href = 'lista.php', 1500);
            } else {
                toastr.error(response.message || 'Error al eliminar el boletín');
            }
        })
        .fail(function(xhr) {
            toastr.error('Error al procesar la solicitud');
        });
    }
} 