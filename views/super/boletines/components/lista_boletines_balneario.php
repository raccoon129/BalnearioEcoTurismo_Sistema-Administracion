<?php
// Obtener balnearios y sus estadísticas
$balnearios = $boletinController->obtenerBalnearios();
$estadisticasBalnearios = $boletinController->obtenerEstadisticasBalneario();

// Obtener filtros
$filtros = [
    'id_balneario' => $_GET['balneario'] ?? null,
    'estado' => $_GET['estado'] ?? null
];

// Obtener boletines según filtros
$boletines = $boletinController->obtenerTodosBoletinesBalnearios($filtros);
?>

<!-- Vista general de balnearios -->
<div class="row g-4 mb-4">
    <?php foreach ($balnearios as $balneario): ?>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-water me-2"></i>
                    <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                </h5>
                
                <?php
                $estadisticas = array_filter($estadisticasBalnearios, function($est) use ($balneario) {
                    return $est['id_balneario'] == $balneario['id_balneario'];
                });
                $estadisticas = reset($estadisticas) ?: ['borradores' => 0, 'enviados' => 0];
                ?>
                
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Borradores:</span>
                        <span class="badge bg-warning"><?php echo $estadisticas['borradores']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Enviados:</span>
                        <span class="badge bg-success"><?php echo $estadisticas['enviados']; ?></span>
                    </div>
                </div>

                <a href="balneario.php?id=<?php echo $balneario['id_balneario']; ?>" 
                   class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-eye me-1"></i>Ver Boletines
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filtros 
<div class="card mb-4">
    <div class="card-body">
        <form id="filtrosBoletinesBalnearios" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Balneario</label>
                <select class="form-select" name="balneario">
                    <option value="">Todos los balnearios</option>
                    <?php foreach ($balnearios as $balneario): ?>
                    <option value="<?php echo $balneario['id_balneario']; ?>"
                            <?php echo $filtros['id_balneario'] == $balneario['id_balneario'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos los estados</option>
                    <option value="borrador" <?php echo $filtros['estado'] === 'borrador' ? 'selected' : ''; ?>>Borradores</option>
                    <option value="enviado" <?php echo $filtros['estado'] === 'enviado' ? 'selected' : ''; ?>>Enviados</option>
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
-->
<!-- Lista de boletines -->
<div class="row g-4">
    <?php if (!empty($boletines)): ?>
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

                    <h6 class="text-muted mb-3">
                        <i class="bi bi-water me-2"></i>
                        <?php echo htmlspecialchars($boletin['nombre_balneario']); ?>
                    </h6>

                    <div class="mb-3">
                        <small class="text-muted d-block">
                            <i class="bi bi-person me-2"></i>
                            Creado por: <?php echo htmlspecialchars($boletin['nombre_usuario']); ?>
                        </small>
                        <small class="text-muted d-block">
                            <i class="bi bi-calendar2 me-2"></i>
                            <?php echo date('d/m/Y H:i', strtotime($boletin['fecha_envio_boletin'] ?? $boletin['fecha_envio_boletin'])); ?>
                        </small>
                    </div>

                    <p class="card-text">
                        <?php echo nl2br(htmlspecialchars(substr($boletin['contenido_boletin'], 0, 150))); ?>...
                    </p>

                    <div class="d-flex justify-content-end gap-2">
                        <?php if ($boletin['estado_boletin'] === 'borrador'): ?>
                            <button type="button" class="btn btn-sm btn-success" 
                                    onclick="enviarBoletinBalneario(<?php echo $boletin['id_boletin']; ?>)">
                                <i class="bi bi-send me-1"></i>Enviar
                            </button>
                            <a href="editar_boletin_balneario.php?id=<?php echo $boletin['id_boletin']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </a>
                        <?php else: ?>
                            <a href="detalles_boletin_balneario.php?id=<?php echo $boletin['id_boletin']; ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Ver Detalles
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- No se encontraron boletines 
        <div class="col-12">
            <div class="text-center text-muted py-5">
                <i class="bi bi-envelope-x display-1"></i>
                <p class="mt-3">No se encontraron boletines<?php echo $filtros['id_balneario'] ? ' para este balneario' : ''; ?></p>
            </div>
        </div>
        -->
    <?php endif; ?>
</div>

<script>
// Manejar envío del formulario de filtros
$('#filtrosBoletinesBalnearios').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams();

    if (formData.get('balneario')) {
        params.append('balneario', formData.get('balneario'));
    }
    if (formData.get('estado')) {
        params.append('estado', formData.get('estado'));
    }

    window.location.search = params.toString();
});
</script> 