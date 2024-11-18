<!-- Modal de Confirmación de Envío -->
<div class="modal fade" id="modalEnvioBoletin" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Envío de Boletín</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Este boletín será enviado a todos los suscriptores registrados.
                </div>

                <h6 class="mb-3">Vista previa del boletín:</h6>
                <div class="card">
                    <div class="card-body">
                        <h5 id="previewTitulo" class="card-title"></h5>
                        <div id="previewContenido" class="card-text"></div>
                    </div>
                </div>

                <input type="hidden" id="id_boletin_envio">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarEnvio" class="btn btn-success">
                    <i class="bi bi-send me-2"></i>Enviar Boletín
                </button>
            </div>
        </div>
    </div>
</div> 