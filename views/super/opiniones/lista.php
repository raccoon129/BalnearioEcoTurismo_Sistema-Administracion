<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Opiniones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <!-- DateRangePicker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .opinion-card {
            transition: all 0.3s ease;
        }
        .opinion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .estado-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        .estado-validada { background-color: #198754; color: white; }
        .estado-pendiente { background-color: #ffc107; color: black; }
        .estado-invalidada { background-color: #dc3545; color: white; }
        .valoracion-estrellas {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .foto-opinion {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .card-stats {
            transition: transform 0.2s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
    </style>
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

    $opinionController = new OpinionSuperController($db);
    $balnearios = $opinionController->obtenerBalnearios();
    $estadisticas = $opinionController->obtenerEstadisticas();

    // Obtener filtros de la URL
    $filtros = [
        'id_balneario' => $_GET['balneario'] ?? null,
        'estado_validacion' => $_GET['estado'] ?? null,
        'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
        'fecha_fin' => $_GET['fecha_fin'] ?? null
    ];

    $opiniones = $opinionController->obtenerOpiniones($filtros);
    ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-chat-square-text me-2"></i>Gestión de Opiniones</h2>
            <button class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                <i class="bi bi-funnel me-2"></i>Filtros
            </button>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card card-stats bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-check-circle me-2"></i>Opiniones Validadas
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['validadas']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats bg-warning">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-clock me-2"></i>Pendientes de Validación
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['pendientes']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats bg-danger text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-x-circle me-2"></i>Opiniones Invalidadas
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['invalidadas']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-star me-2"></i>Mejor Valorado
                        </h6>
                        <p class="mb-0">
                            <?php 
                            if ($estadisticas['mejor_balneario']) {
                                echo htmlspecialchars($estadisticas['mejor_balneario']['nombre_balneario']);
                                echo " (" . number_format($estadisticas['mejor_balneario']['promedio'], 1) . " ★)";
                            } else {
                                echo "Sin datos";
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de filtros -->
        <div class="collapse mb-4" id="filtrosCollapse">
            <div class="card">
                <div class="card-body">
                    <form id="filtrosForm" class="row g-3">
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" <?php echo $filtros['estado_validacion'] === 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                                <option value="validada" <?php echo $filtros['estado_validacion'] === 'validada' ? 'selected' : ''; ?>>Validadas</option>
                                <option value="invalidada" <?php echo $filtros['estado_validacion'] === 'invalidada' ? 'selected' : ''; ?>>Invalidadas</option>
                            </select>
                        </div>
                        <div class="col-md-4">
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

        <!-- Resumen de filtros aplicados -->
        <?php if (!empty($filtros['id_balneario']) || !empty($filtros['estado_validacion']) || !empty($filtros['fecha_inicio'])): ?>
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-funnel-fill me-2"></i>
                <div>
                    <strong>Filtros aplicados:</strong>
                    <span class="ms-2">
                        <?php
                        $filtrosAplicados = [];
                        
                        if (!empty($filtros['id_balneario'])) {
                            foreach ($balnearios as $balneario) {
                                if ($balneario['id_balneario'] == $filtros['id_balneario']) {
                                    $filtrosAplicados[] = "Balneario: " . htmlspecialchars($balneario['nombre_balneario']);
                                    break;
                                }
                            }
                        }
                        
                        if (!empty($filtros['estado_validacion'])) {
                            $estados = [
                                'pendiente' => 'Pendientes',
                                'validada' => 'Validadas',
                                'invalidada' => 'Invalidadas'
                            ];
                            $filtrosAplicados[] = "Estado: " . $estados[$filtros['estado_validacion']];
                        }
                        
                        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
                            $fechaInicio = date('d/m/Y', strtotime($filtros['fecha_inicio']));
                            $fechaFin = date('d/m/Y', strtotime($filtros['fecha_fin']));
                            $filtrosAplicados[] = "Periodo: {$fechaInicio} - {$fechaFin}";
                        }
                        
                        echo implode(" | ", $filtrosAplicados);
                        ?>
                    </span>
                </div>
            </div>
            <a href="lista.php" class="btn btn-outline-info btn-sm position-absolute end-0 me-4">
                <i class="bi bi-x-circle me-1"></i>Limpiar filtros
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Lista de opiniones -->
        <div class="row g-4">
            <?php foreach ($opiniones as $opinion): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card opinion-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title"><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></h5>
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

                        <h6 class="text-muted mb-3">
                            <i class="bi bi-water me-2"></i>
                            <a href="balneario.php?id=<?php echo $opinion['id_balneario']; ?>" 
                               class="text-decoration-none text-muted">
                                <?php echo htmlspecialchars($opinion['nombre_balneario']); ?>
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </h6>

                        <?php if ($opinion['url_foto_opinion']): ?>
                            <img src="../../../<?php echo htmlspecialchars($opinion['url_foto_opinion']); ?>" 
                                 class="foto-opinion mb-3" alt="Foto de la opinión">
                        <?php endif; ?>

                        <p class="card-text"><?php echo htmlspecialchars($opinion['opinion']); ?></p>

                        <div class="mb-3 valoracion-estrellas">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= $opinion['valoracion'] ? '-fill' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-calendar2 me-1"></i>
                                <?php echo date('d/m/Y', strtotime($opinion['fecha_registro_opinion'])); ?>
                            </small>
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
                    <p class="mt-3">No se encontraron opiniones</p>
                </div>
            </div>
            <?php endif; ?>
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
            const params = new URLSearchParams();

            if (formData.get('balneario')) {
                params.append('balneario', formData.get('balneario'));
            }
            if (formData.get('estado')) {
                params.append('estado', formData.get('estado'));
            }
            if (formData.get('periodo')) {
                const [inicio, fin] = formData.get('periodo').split(' - ');
                params.append('fecha_inicio', inicio);
                params.append('fecha_fin', fin);
            }

            window.location.search = params.toString();
        });
    });

    // Función para validar/invalidar opinión
    function validarOpinion(idOpinion, validar) {
        if (!confirm(`¿Está seguro de ${validar ? 'validar' : 'invalidar'} esta opinión?`)) return;

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
            }
        })
        .fail(function() {
            toastr.error('Error al procesar la solicitud');
        });
    }

    // Función para limpiar filtros
    function limpiarFiltros() {
        window.location.href = 'lista.php';
    }
    </script>
</body>
</html> 