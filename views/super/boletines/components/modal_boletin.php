<!-- Modal para Crear/Editar Boletín -->
<div class="modal fade" id="modalBoletin" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Nuevo Boletín</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formBoletin" action="../../../controllers/super/boletines/guardar.php" method="POST">
                    <input type="hidden" name="id_boletin" id="id_boletin">
                    
                    <!-- Título del Boletín -->
                    <div class="mb-3">
                        <label class="form-label">Título del Boletín <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="titulo_boletin" required
                               placeholder="Ingrese el título del boletín">
                    </div>

                    <!-- Contenido del Boletín -->
                    <div class="mb-3">
                        <label class="form-label">Contenido <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="contenido_boletin" rows="6" required
                                  placeholder="Escriba el contenido del boletín..."></textarea>
                        <div class="form-text">
                            El contenido se enviará como está escrito. Asegúrese de revisar el formato.
                        </div>
                    </div>

                    <!-- Destinatarios -->
                    <div class="mb-3">
                        <label class="form-label">Destinatarios <span class="text-danger">*</span></label>
                        <div class="form-text mb-2">
                            Seleccione al menos un tipo de destinatario si desea enviar el boletín inmediatamente.
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="destinatarios[]" 
                                   value="superadmin" id="checkSuperAdmin">
                            <label class="form-check-label" for="checkSuperAdmin">
                                SuperAdministradores
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="destinatarios[]" 
                                   value="admin" id="checkAdmin">
                            <label class="form-check-label" for="checkAdmin">
                                Administradores de Balnearios
                            </label>
                        </div>
                    </div>

                    <!-- Guardar como borrador -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="es_borrador" 
                               id="checkBorrador" checked>
                        <label class="form-check-label" for="checkBorrador">
                            Guardar como borrador
                        </label>
                        <div class="form-text">
                            Si está marcado, el boletín se guardará como borrador y podrá enviarlo más tarde.
                        </div>
                    </div>

                    <!-- Vista previa del contenido -->
                    <div class="mb-3 border rounded p-3 bg-light">
                        <h6 class="mb-3">Vista Previa</h6>
                        <div id="vistaPreviaBoletin" class="p-3 bg-white rounded">
                            <div id="vistaPreviewTitulo" class="h5 mb-3"></div>
                            <div id="vistaPreviewContenido"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formBoletin" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Boletín
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Actualizar vista previa al escribir
    $('input[name="titulo_boletin"], textarea[name="contenido_boletin"]').on('input', function() {
        $('#vistaPreviewTitulo').text($('input[name="titulo_boletin"]').val());
        $('#vistaPreviewContenido').html($('textarea[name="contenido_boletin"]').val().replace(/\n/g, '<br>'));
    });

    // Manejar checkbox de borrador
    $('#checkBorrador').on('change', function() {
        const destinatarios = $('input[name="destinatarios[]"]');
        if (this.checked) {
            destinatarios.prop('required', false);
        } else {
            destinatarios.prop('required', true);
        }
    });

    // Limpiar formulario al cerrar modal
    $('#modalBoletin').on('hidden.bs.modal', function() {
        $('#formBoletin')[0].reset();
        $('#id_boletin').val('');
        $('#vistaPreviewTitulo').text('');
        $('#vistaPreviewContenido').html('');
        $('#modalTitulo').text('Nuevo Boletín');
        $('button[type="submit"]').prop('disabled', false);
    });
});
</script> 