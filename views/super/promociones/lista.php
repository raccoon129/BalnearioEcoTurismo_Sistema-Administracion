<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Promociones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <!-- DateRangePicker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .card-stats {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .estado-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        .filtros-seccion {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .promocion-card {
            transition: all 0.3s ease;
        }
        .promocion-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .estado-activa {
            background-color: #198754;
            color: white;
        }
        .estado-proxima {
            background-color: #0dcaf0;
            color: white;
        }
        .estado-finalizada {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/promociones/PromocionSuperController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    $promocionController = new PromocionSuperController($db);
    $balnearios = $promocionController->obtenerBalnearios();
    $estadisticas = $promocionController->obtenerEstadisticas();

    // Obtener filtros de la URL
    $filtros = [
        'id_balneario' => $_GET['balneario'] ?? null,
        'estado' => $_GET['estado'] ?? null,
        'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
        'fecha_fin' => $_GET['fecha_fin'] ?? null
    ];

    $promociones = $promocionController->obtenerPromociones($filtros);
    ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-tags me-2"></i>Gestionar Promociones</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                    <i class="bi bi-funnel me-2"></i>Filtros
                </button>
                <a href="ver.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Nueva Promoción
                </a>
            </div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card card-stats bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-calendar-check me-2"></i>Promociones Activas
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['activas']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-calendar-plus me-2"></i>Próximas a Iniciar
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['proximas']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats bg-secondary text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-calendar-x me-2"></i>Finalizadas
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['finalizadas']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-trophy me-2"></i>Balneario más Activo
                        </h6>
                        <p class="mb-0">
                            <?php 
                            if ($estadisticas['balneario_mas_activo']) {
                                echo htmlspecialchars($estadisticas['balneario_mas_activo']['nombre_balneario']);
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
                                <option value="activa" <?php echo $filtros['estado'] === 'activa' ? 'selected' : ''; ?>>Activas</option>
                                <option value="proxima" <?php echo $filtros['estado'] === 'proxima' ? 'selected' : ''; ?>>Próximas</option>
                                <option value="finalizada" <?php echo $filtros['estado'] === 'finalizada' ? 'selected' : ''; ?>>Finalizadas</option>
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

        <!-- Nuevo: Resumen de filtros aplicados -->
        <?php if (!empty($filtros['id_balneario']) || !empty($filtros['estado']) || !empty($filtros['fecha_inicio'])): ?>
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-funnel-fill me-2"></i>
                <div>
                    <strong>Filtros aplicados:</strong>
                    <span class="ms-2">
                        <?php
                        $filtrosAplicados = [];
                        
                        // Filtro de balneario
                        if (!empty($filtros['id_balneario'])) {
                            foreach ($balnearios as $balneario) {
                                if ($balneario['id_balneario'] == $filtros['id_balneario']) {
                                    $filtrosAplicados[] = "Balneario: " . htmlspecialchars($balneario['nombre_balneario']);
                                    break;
                                }
                            }
                        }
                        
                        // Filtro de estado
                        if (!empty($filtros['estado'])) {
                            $estados = [
                                'activa' => 'Activas',
                                'proxima' => 'Próximas',
                                'finalizada' => 'Finalizadas'
                            ];
                            $filtrosAplicados[] = "Estado: " . $estados[$filtros['estado']];
                        }
                        
                        // Filtro de fechas
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

        <!-- Mostrar total de resultados -->
        <div class="text-muted mb-4">
            Se encontraron <?php echo count($promociones); ?> promociones
            <?php 
            if (!empty($filtrosAplicados)) {
                echo "con los filtros seleccionados";
            } else {
                echo "en total";
            }
            ?>
        </div>

        <!-- Lista de promociones -->
        <div class="row g-4">
            <?php foreach ($promociones as $promocion): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card promocion-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title"><?php echo htmlspecialchars($promocion['titulo_promocion']); ?></h5>
                            <span class="badge estado-<?php echo $promocion['estado_promocion']; ?> estado-badge">
                                <?php 
                                switch ($promocion['estado_promocion']) {
                                    case 'activa':
                                        echo '<i class="bi bi-check-circle me-1"></i>Activa';
                                        break;
                                    case 'proxima':
                                        echo '<i class="bi bi-clock me-1"></i>Próxima';
                                        break;
                                    case 'finalizada':
                                        echo '<i class="bi bi-x-circle me-1"></i>Finalizada';
                                        break;
                                }
                                ?>
                            </span>
                        </div>

                        <h6 class="text-muted mb-3">
                            <i class="bi bi-water me-2"></i>
                            <?php echo htmlspecialchars($promocion['nombre_balneario']); ?>
                        </h6>

                        <p class="card-text">
                            <?php echo htmlspecialchars(substr($promocion['descripcion_promocion'], 0, 100)) . '...'; ?>
                        </p>

                        <div class="mb-3">
                            <small class="text-muted d-block">
                                <i class="bi bi-calendar-event me-2"></i>
                                Inicio: <?php echo date('d/m/Y', strtotime($promocion['fecha_inicio_promocion'])); ?>
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-calendar-check me-2"></i>
                                Fin: <?php echo date('d/m/Y', strtotime($promocion['fecha_fin_promocion'])); ?>
                            </small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="ver.php?id=<?php echo $promocion['id_promocion']; ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($promociones)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox display-1"></i>
                    <p class="mt-3">No se encontraron promociones</p>
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

    // Función para limpiar filtros
    function limpiarFiltros() {
        window.location.href = 'lista.php';
    }
    </script>
</body>
</html> 