<?php
// Obtener estadísticas y boletines del sistema
$estadisticas = $boletinController->obtenerEstadisticas();
$boletines = $boletinController->obtenerBoletinesSuperAdmin();
?>

<!-- Estadísticas -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-envelope me-2"></i>Total de Boletines
                </h6>
                <h2 class="mb-0"><?php echo $estadisticas['total']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-file-earmark me-2"></i>Borradores
                </h6>
                <h2 class="mb-0"><?php echo $estadisticas['borradores']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-send me-2"></i>Enviados
                </h6>
                <h2 class="mb-0"><?php echo $estadisticas['enviados']; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form id="filtrosBoletinesSistema" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos los estados</option>
                    <option value="borrador">Borradores</option>
                    <option value="enviado">Enviados</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Creador</label>
                <select class="form-select" name="creador">
                    <option value="">Todos los creadores</option>
                    <option value="propio">Mis boletines</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-2"></i>Filtrar
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de boletines -->
<div class="row g-4">
    <?php foreach ($boletines as $boletin): ?>
    <div class="col-md-6">
        <div class="card boletin-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title"><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></h5>
                    <span class="badge estado-<?php echo $boletin['estado_boletin']; ?> estado-badge">
                        <?php if ($boletin['estado_boletin'] === 'borrador'): ?>
                            <i class="bi bi-file-earmark me-1"></i>Borrador
                        <?php else: ?>
                            <i class="bi bi-send me-1"></i>Enviado
                        <?php endif; ?>
                    </span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">
                        <i class="bi bi-person me-2"></i>
                        Creado por: <?php echo htmlspecialchars($boletin['nombre_usuario']); ?>
                    </small>
                </div>

                <p class="card-text">
                    <?php echo nl2br(htmlspecialchars(substr($boletin['contenido_boletin'], 0, 200))); ?>...
                </p>

                <div class="d-flex justify-content-end gap-2">
                    <?php if ($boletin['estado_boletin'] === 'borrador'): ?>
                        <a href="editar_boletin_superadministrador.php?id=<?php echo $boletin['id_boletin']; ?>" 
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>
                        <button type="button" class="btn btn-sm btn-success" 
                                onclick="mostrarModalEnvio(<?php echo $boletin['id_boletin']; ?>)">
                            <i class="bi bi-send me-1"></i>Enviar
                        </button>
                    <?php else: ?>
                        <a href="detalles.php?id=<?php echo $boletin['id_boletin']; ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Ver Detalles
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($boletines)): ?>
    <div class="col-12">
        <div class="text-center text-muted py-5">
            <i class="bi bi-envelope-x display-1"></i>
            <p class="mt-3">No se encontraron boletines</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmación de envío -->
<?php include 'modal_envio_boletin_superadministrador.php'; ?> 