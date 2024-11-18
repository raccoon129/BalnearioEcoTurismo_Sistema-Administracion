$(document).ready(function() {
    // Configuración de toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    // Manejar envío del formulario de nueva promoción
    $('#formPromocion').on('submit', function(e) {
        e.preventDefault();
        console.log('Iniciando envío del formulario de promoción');

        // Validar campos requeridos
        const titulo = $('input[name="titulo"]').val().trim();
        const descripcion = $('textarea[name="descripcion"]').val().trim();
        const fechaInicio = $('input[name="fecha_inicio"]').val();
        const fechaFin = $('input[name="fecha_fin"]').val();

        if (!titulo || !descripcion || !fechaInicio || !fechaFin) {
            toastr.error('Todos los campos son requeridos');
            return false;
        }

        // Validar fechas
        if (fechaFin < fechaInicio) {
            toastr.error('La fecha de fin no puede ser anterior a la fecha de inicio');
            return false;
        }

        // Obtener datos del formulario
        const formData = new FormData(this);

        // Deshabilitar botón y mostrar indicador de carga
        const btnSubmit = $(this).find('button[type="submit"]');
        const btnText = btnSubmit.html();
        
        btnSubmit.prop('disabled', true)
                .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

        // Enviar petición AJAX
        $.ajax({
            url: '../../../controllers/balneario/promociones/guardar.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function(response) {
            console.log('Respuesta del servidor:', response);
            
            if (response.success) {
                // Cerrar modal
                $('#modalPromocion').modal('hide');
                
                // Mostrar mensaje de éxito
                toastr.success(response.message);
                
                // Recargar página después de un breve delay
                setTimeout(() => location.reload(), 1500);
            } else {
                console.error('Error en la respuesta:', response.message);
                toastr.error(response.message || 'Error al guardar la promoción');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error en la petición:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            
            let errorMessage = 'Error al guardar la promoción';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch (e) {
                console.error('Error al parsear la respuesta:', xhr.responseText);
            }
            
            toastr.error(errorMessage);
        })
        .always(function() {
            console.log('Finalizando petición');
            btnSubmit.prop('disabled', false).html(btnText);
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalPromocion').on('hidden.bs.modal', function() {
        $('#formPromocion')[0].reset();
    });
}); 