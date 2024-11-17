<?php
// Obtener balnearios y sus estadísticas
$balnearios = $boletinController->obtenerBalnearios();

// Obtener filtros
$filtros = [
    'id_balneario' => $_GET['balneario'] ?? null,
    'estado' => $_GET['estado'] ?? null
];

// Obtener boletines según filtros
$boletines = $boletinController->obtenerTodosBoletinesBalnearios($filtros);

// Calcular estadísticas por balneario
$estadisticasBalnearios = [];
foreach ($boletines as $boletin) {
    $id_balneario = $boletin['id_balneario'];
    
    if (!isset($estadisticasBalnearios[$id_balneario])) {
        $estadisticasBalnearios[$id_balneario] = [
            'id_balneario' => $id_balneario,
            'nombre_balneario' => $boletin['nombre_balneario'],
            'total_boletines' => 0,
            'borradores' => 0,
            'enviados' => 0
        ];
    }
    
    $estadisticasBalnearios[$id_balneario]['total_boletines']++;
    if ($boletin['fecha_envio_boletin'] === null) {
        $estadisticasBalnearios[$id_balneario]['borradores']++;
    } else {
        $estadisticasBalnearios[$id_balneario]['enviados']++;
    }
}
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
                $estadisticas = $estadisticasBalnearios[$balneario['id_balneario']] ?? [
                    'borradores' => 0,
                    'enviados' => 0,
                    'total_boletines' => 0
                ];
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
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total:</span>
                        <span class="badge bg-primary"><?php echo $estadisticas['total_boletines']; ?></span>
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

<!-- Lista de boletines -->
<div class="row g-4">
    <?php if (!empty($boletines)): ?>
        <?php foreach ($boletines as $boletin): ?>
        <div class="col-md-6">
            <div class="card boletin-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title"><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></h5>
                        <span class="badge estado-<?php echo $boletin['fecha_envio_boletin'] === null ? 'borrador' : 'enviado'; ?> estado-badge">
                            <?php if ($boletin['fecha_envio_boletin'] === null): ?>
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
                            <?php 
                            $fecha = $boletin['fecha_envio_boletin'] ?? $boletin['fecha_creacion'];
                            echo date('d/m/Y H:i', strtotime($fecha)); 
                            ?>
                        </small>
                    </div>

                    <p class="card-text">
                        <?php echo nl2br(htmlspecialchars(substr($boletin['contenido_boletin'], 0, 150))); ?>...
                    </p>

                    <div class="d-flex justify-content-end gap-2">
                        <?php if ($boletin['fecha_envio_boletin'] === null): ?>
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
        <div class="col-12">
            <div class="text-center text-muted py-5">
                <i class="bi bi-envelope-x display-1"></i>
                <p class="mt-3">No se encontraron boletines<?php echo $filtros['id_balneario'] ? ' para este balneario' : ''; ?></p>
            </div>
        </div>
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