// Configuración de DataTables
$(document).ready(function() {
    $('#tablaContactos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true
    });
});

// Configuración global de toastr
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 3000
};

// Función para confirmar eliminación
function confirmarEliminacion(idContacto) {
    if (confirm('¿Está seguro que desea eliminar este contacto? Esta acción no se puede deshacer.')) {
        $.ajax({
            url: '../../../controllers/super/contactos/eliminar.php',
            method: 'POST',
            data: { id_contacto: idContacto },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                // Recargar la página después de un breve delay
                setTimeout(() => window.location.reload(), 1500);
            } else {
                toastr.error(response.message || 'Error al eliminar el contacto');
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
        });
    }
} 