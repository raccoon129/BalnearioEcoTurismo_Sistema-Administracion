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

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Este boletín será enviado a todos los suscriptores registrados.
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer. El boletín será enviado inmediatamente.
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