// Configuración global de toastr
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "timeOut": "3000",
    "positionClass": "toast-top-right"
};

// Función para guardar borrador (usado en el modal de nuevo boletín)
function guardarBorrador(formData) {
    const btnSubmit = $('#formBoletin button[type="submit"]');
    const btnHtml = btnSubmit.html();
    btnSubmit.prop('disabled', true)
            .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

    $.ajax({
        url: '../../../controllers/super/boletines/guardar.php',
        method: 'POST',
        data: formData,
        dataType: 'json'
    })
    .done(function(response) {
        if (response.success) {
            toastr.success(response.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            toastr.error(response.message || 'Error al guardar el boletín');
            btnSubmit.prop('disabled', false).html(btnHtml);
        }
    })
    .fail(function() {
        toastr.error('Error al procesar la solicitud');
        btnSubmit.prop('disabled', false).html(btnHtml);
    });
}

// Función para actualizar borrador
function actualizarBorrador(formData) {
    const btnSubmit = $('#formEditarBoletin button[type="submit"]');
    const btnHtml = btnSubmit.html();
    btnSubmit.prop('disabled', true)
            .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

    $.ajax({
        url: '../../../controllers/super/boletines/actualizar.php',
        method: 'POST',
        data: formData,
        dataType: 'json'
    })
    .done(function(response) {
        if (response.success) {
            toastr.success(response.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            toastr.error(response.message || 'Error al actualizar el boletín');
            btnSubmit.prop('disabled', false).html(btnHtml);
        }
    })
    .fail(function() {
        toastr.error('Error al procesar la solicitud');
        btnSubmit.prop('disabled', false).html(btnHtml);
    });
}

// Función para enviar boletín
function enviarBoletin(formData) {
    const btnSubmit = $('#formEnvioBoletin button[type="submit"]');
    const btnHtml = btnSubmit.html();
    btnSubmit.prop('disabled', true)
            .html('<i class="bi bi-hourglass-split me-2"></i>Enviando...');

    $.ajax({
        url: '../../../controllers/super/boletines/enviar.php',
        method: 'POST',
        data: formData,
        dataType: 'json'
    })
    .done(function(response) {
        if (response.success) {
            toastr.success(response.message);
            $('#modalEnvioBoletin').modal('hide');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            toastr.error(response.message || 'Error al enviar el boletín');
            btnSubmit.prop('disabled', false).html(btnHtml);
        }
    })
    .fail(function() {
        toastr.error('Error al procesar la solicitud');
        btnSubmit.prop('disabled', false).html(btnHtml);
    });
}

// Manejadores de eventos para los formularios
$(document).ready(function() {
    // Formulario de nuevo boletín
    $('#formBoletin').on('submit', function(e) {
        e.preventDefault();
        guardarBorrador($(this).serialize());
    });

    // Formulario de edición
    $('#formEditarBoletin').on('submit', function(e) {
        e.preventDefault();
        actualizarBorrador($(this).serialize());
    });

    // Formulario de envío
    $('#formEnvioBoletin').on('submit', function(e) {
        e.preventDefault();
        if (!$('input[name="destinatarios[]"]:checked').length) {
            toastr.error('Debe seleccionar al menos un tipo de destinatario');
            return;
        }
        enviarBoletin($(this).serialize());
    });

    // Limpiar formularios al cerrar modales
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').trigger('reset');
        $(this).find('.preview-content').empty();
    });
}); 