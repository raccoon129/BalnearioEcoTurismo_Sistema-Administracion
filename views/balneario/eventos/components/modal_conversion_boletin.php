<!-- Modal de Confirmación de Conversión -->
<div class="modal fade" id="modalConfirmarConversion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Convertir a Boletín</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Desea crear un boletín con la información de este evento?</p>
                <p>Se creará un borrador que podrá revisar antes de enviarlo.</p>
                <div id="previewContenido" class="mt-3 p-3 bg-light rounded">
                    <!-- Aquí se mostrará la vista previa del contenido -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarConversion">
                    <i class="bi bi-envelope me-2"></i>Crear Boletín
                </button>
            </div>
        </div>
    </div>
</div> 