<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opiniones del Balneario</title>
    <!-- ... (mismos estilos que lista.php) ... -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <!-- DateRangePicker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/opiniones/OpinionSuperController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Obtener ID del balneario
    $id_balneario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id_balneario) {
        header('Location: lista.php');
        exit();
    }

    $opinionController = new OpinionSuperController($db);
    $balneario = $opinionController->obtenerBalneario($id_balneario);
    if (!$balneario) {
        header('Location: lista.php');
        exit();
    }

    $estadisticas = $opinionController->obtenerEstadisticas($id_balneario);
    $filtros = [
        'id_balneario' => $id_balneario,
        'estado_validacion' => $_GET['estado'] ?? null,
        'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
        'fecha_fin' => $_GET['fecha_fin'] ?? null
    ];

    $opiniones = $opinionController->obtenerOpiniones($filtros);
    ?>

    <div class="container-fluid py-4">
        <!-- Encabezado con nombre del balneario -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>
                    <i class="bi bi-chat-square-text me-2"></i>
                    Opiniones de <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                </h2>
                <p class="text-muted mb-0">
                    Valoración promedio: 
                    <span class="text-warning">
                        <?php echo number_format($estadisticas['promedio_valoracion'], 1); ?> 
                        <i class="bi bi-star-fill"></i>
                    </span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                    <i class="bi bi-funnel me-2"></i>Filtros
                </button>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <!-- Tarjetas de estadísticas específicas del balneario -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card card-stats bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-check-circle me-2"></i>Opiniones Validadas
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['validadas']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats bg-warning">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-clock me-2"></i>Pendientes
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['pendientes']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats bg-danger text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-x-circle me-2"></i>Invalidadas
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['invalidadas']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de filtros (solo estado y fecha) -->
        <div class="collapse mb-4" id="filtrosCollapse">
            <div class="card">
                <div class="card-body">
                    <form id="filtrosForm" class="row g-3">
                        <input type="hidden" name="id" value="<?php echo $id_balneario; ?>">
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" <?php echo $filtros['estado_validacion'] === 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                                <option value="validada" <?php echo $filtros['estado_validacion'] === 'validada' ? 'selected' : ''; ?>>Validadas</option>
                                <option value="invalidada" <?php echo $filtros['estado_validacion'] === 'invalidada' ? 'selected' : ''; ?>>Invalidadas</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Periodo</label>
                            <input type="text" class="form-control" id="daterange" name="periodo" 
                                   value="<?php echo $filtros['fecha_inicio'] ? $filtros['fecha_inicio'] . ' - ' . $filtros['fecha_fin'] : ''; ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
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
        </div>

        <!-- Lista de opiniones -->
        <div class="row g-4">
            <?php foreach ($opiniones as $opinion): ?>
            <div class="col-md-6">
                <div class="card opinion-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></h5>
                                <div class="valoracion-estrellas">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $opinion['valoracion'] ? '-fill' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <span class="badge estado-<?php echo $opinion['estado_validacion']; ?> estado-badge">
                                <?php 
                                switch ($opinion['estado_validacion']) {
                                    case 'validada':
                                        echo '<i class="bi bi-check-circle me-1"></i>Validada';
                                        break;
                                    case 'pendiente':
                                        echo '<i class="bi bi-clock me-1"></i>Pendiente';
                                        break;
                                    case 'invalidada':
                                        echo '<i class="bi bi-x-circle me-1"></i>Invalidada';
                                        break;
                                }
                                ?>
                            </span>
                        </div>

                        <?php if ($opinion['url_foto_opinion']): ?>
                            <div class="position-relative mb-3">
                                <img src="../../../<?php echo htmlspecialchars($opinion['url_foto_opinion']); ?>" 
                                     class="foto-opinion w-100" alt="Foto de la opinión"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#modalImagen"
                                     data-imagen="../../../<?php echo htmlspecialchars($opinion['url_foto_opinion']); ?>"
                                     style="cursor: pointer;">
                                <div class="position-absolute bottom-0 end-0 m-2">
                                    <span class="badge bg-dark">
                                        <i class="bi bi-zoom-in"></i> Click para ampliar
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <p class="card-text"><?php echo nl2br(htmlspecialchars($opinion['opinion'])); ?></p>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted d-block">
                                    <i class="bi bi-calendar2 me-1"></i>
                                    <?php echo date('d/m/Y', strtotime($opinion['fecha_registro_opinion'])); ?>
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-clock me-1"></i>
                                    <?php echo date('h:i A', strtotime($opinion['fecha_registro_opinion'])); ?>
                                </small>
                            </div>
                            <div class="btn-group">
                                <?php if ($opinion['estado_validacion'] !== 'validada'): ?>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="validarOpinion(<?php echo $opinion['id_opinion']; ?>, true)"
                                            title="Validar opinión">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($opinion['estado_validacion'] !== 'invalidada'): ?>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="validarOpinion(<?php echo $opinion['id_opinion']; ?>, false)"
                                            title="Invalidar opinión">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($opiniones)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-chat-square-text display-1"></i>
                    <p class="mt-3">No se encontraron opiniones para este balneario</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para ver imagen -->
    <div class="modal fade" id="modalImagen" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Imagen de la Opinión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <img src="" class="img-fluid w-100" id="imagenAmpliada" alt="Imagen ampliada">
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
    $(document).ready(function() {
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000"
        };

        // Inicializar DateRangePicker
        $('#daterange').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Rango personalizado',
                weekLabel: 'S',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },
            opens: 'left',
            autoUpdateInput: false
        });

        // Manejar selección de fechas
        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Manejar envío del formulario de filtros
        $('#filtrosForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(window.location.search);
            
            params.set('id', formData.get('id'));
            if (formData.get('estado')) params.set('estado', formData.get('estado'));
            else params.delete('estado');
            
            if (formData.get('periodo')) {
                const [inicio, fin] = formData.get('periodo').split(' - ');
                params.set('fecha_inicio', inicio);
                params.set('fecha_fin', fin);
            } else {
                params.delete('fecha_inicio');
                params.delete('fecha_fin');
            }

            window.location.search = params.toString();
        });

        // Manejar modal de imagen
        $('#modalImagen').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const imagen = button.data('imagen');
            $('#imagenAmpliada').attr('src', imagen);
        });
    });

    // Función para validar/invalidar opinión
    function validarOpinion(idOpinion, validar) {
        if (!confirm(`¿Está seguro de ${validar ? 'validar' : 'invalidar'} esta opinión?`)) return;

        const btn = event.currentTarget;
        const btnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

        $.post('../../../controllers/super/opiniones/validar.php', {
            id_opinion: idOpinion,
            validar: validar
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(response.message);
                btn.disabled = false;
                btn.innerHTML = btnHtml;
            }
        })
        .fail(function() {
            toastr.error('Error al procesar la solicitud');
            btn.disabled = false;
            btn.innerHTML = btnHtml;
        });
    }

    // Función para limpiar filtros
    function limpiarFiltros() {
        window.location.href = `balneario.php?id=<?php echo $id_balneario; ?>`;
    }
    </script>
</body>
</html> 