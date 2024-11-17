<!-- Modal para Envío de Boletín -->
<div class="modal fade" id="modalEnvioBoletin" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enviar Boletín</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEnvioBoletin">
                    <input type="hidden" name="id_boletin" id="id_boletin_envio">

                    <!-- Vista previa del boletín -->
                    <div class="card mb-4">
                        <div class="card-header">
                            Vista Previa del Boletín
                        </div>
                        <div class="card-body">
                            <h5 id="previewTitulo" class="mb-3"></h5>
                            <div id="previewContenido"></div>
                        </div>
                    </div>

                    <!-- Selección de destinatarios -->
                    <div class="mb-3">
                        <label class="form-label">Seleccione los destinatarios <span class="text-danger">*</span></label>
                        <div class="form-text mb-2">
                            Debe seleccionar al menos un tipo de destinatario.
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="destinatarios[]" 
                                   value="superadmin" id="checkSuperAdmin">
                            <label class="form-check-label" for="checkSuperAdmin">
                                SuperAdministradores
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="destinatarios[]" 
                                   value="admin" id="checkAdmin">
                            <label class="form-check-label" for="checkAdmin">
                                Administradores de Balnearios
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="destinatarios[]" 
                                   value="suscriptores" id="checkSuscriptores">
                            <label class="form-check-label" for="checkSuscriptores">
                                Suscriptores
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer. El boletín será enviado a todos los destinatarios seleccionados.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarEnvio" class="btn btn-success">
                    <i class="bi bi-send me-2"></i>Confirmar Envío
                </button>
            </div>
        </div>
    </div>
</div> 