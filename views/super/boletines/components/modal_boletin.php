<!-- Modal para Crear Boletín -->
<div class="modal fade" id="modalBoletin" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Boletín</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formBoletin" action="../../../controllers/super/boletines/guardar.php" method="POST">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        El boletín se guardará como borrador y podrá enviarlo más tarde.
                    </div>

                    <!-- Título del Boletín -->
                    <div class="mb-3">
                        <label class="form-label">Título del Boletín <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="titulo_boletin" required
                               placeholder="Ingrese un título descriptivo">
                        <div class="form-text">
                            El título debe ser claro y conciso.
                        </div>
                    </div>

                    <!-- Contenido del Boletín -->
                    <div class="mb-3">
                        <label class="form-label">Contenido <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="contenido_boletin" rows="8" required
                                  placeholder="Escriba el contenido del boletín..."></textarea>
                        <div class="form-text">
                            Escriba el contenido completo del boletín. Puede usar saltos de línea para mejor organización.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formBoletin" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Borrador
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Actualizar vista previa en tiempo real
    function actualizarVistaPrevia() {
        $('#previewTitulo').text($('input[name="titulo_boletin"]').val());
        $('#previewContenido').html($('textarea[name="contenido_boletin"]').val().replace(/\n/g, '<br>'));
    }

    // Eventos para actualizar vista previa
    $('input[name="titulo_boletin"], textarea[name="contenido_boletin"]').on('input', actualizarVistaPrevia);

    // Manejar envío del formulario
    $('#formBoletin').on('submit', function(e) {
        e.preventDefault();

        const btnSubmit = $(this).find('button[type="submit"]');
        const btnHtml = btnSubmit.html();
        btnSubmit.prop('disabled', true)
                .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#modalBoletin').modal('hide');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(response.message);
                btnSubmit.prop('disabled', false).html(btnHtml);
            }
        })
        .fail(function() {
            toastr.error('Error al procesar la solicitud');
            btnSubmit.prop('disabled', false).html(btnHtml);
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalBoletin').on('hidden.bs.modal', function() {
        $('#formBoletin')[0].reset();
        $('#previewTitulo').text('');
        $('#previewContenido').html('');
    });

    // Solo mantener la actualización de la vista previa si existe
    if ($('#previewTitulo').length && $('#previewContenido').length) {
        $('input[name="titulo_boletin"], textarea[name="contenido_boletin"]').on('input', function() {
            $('#previewTitulo').text($('input[name="titulo_boletin"]').val());
            $('#previewContenido').html($('textarea[name="contenido_boletin"]').val().replace(/\n/g, '<br>'));
        });
    }
});
</script> 