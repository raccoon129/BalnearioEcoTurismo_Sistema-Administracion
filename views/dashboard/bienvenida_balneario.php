<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .welcome-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            color: white;
            border-radius: 15px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .balneario-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stats-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }

        .activity-card {
            border: none;
            border-radius: 12px;
            height: 100%;
        }

        .activity-item {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: background-color 0.2s;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
        }

        .rating-stars {
            color: #ffc107;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../config/database.php';
    require_once '../../controllers/balneario/inicio/EstadisticasController.php';
    
    session_start();
    $database = new Database();
    $db = $database->getConnection();
    
    $estadisticasController = new EstadisticasController($db);
    $id_balneario = $_SESSION['id_balneario'];
    
    $reservas_proximas = $estadisticasController->obtenerReservasProximas($id_balneario);
    $opiniones_recientes = $estadisticasController->obtenerOpinionesRecientes($id_balneario);
    $datos_balneario = $estadisticasController->obtenerDatosBalneario($id_balneario);
    $estadisticas = $estadisticasController->obtenerEstadisticasGenerales($id_balneario);
    ?>

    <div class="container py-4">
        <!-- Banner de Bienvenida -->
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="balneario-title"><?php echo htmlspecialchars($datos_balneario['nombre_balneario']); ?></h1>
                    <p class="lead mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="mb-2">
                        <i class="bi bi-clock me-2"></i>
                        <?php echo substr($datos_balneario['horario_apertura'], 0, 5); ?> - 
                        <?php echo substr($datos_balneario['horario_cierre'], 0, 5); ?>
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-telephone me-2"></i>
                        <?php echo htmlspecialchars($datos_balneario['telefono_balneario']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo $estadisticas['reservas_hoy']; ?></h3>
                        <p class="text-muted mb-0">Reservas Hoy</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-star"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo number_format($estadisticas['valoracion_promedio'], 1); ?></h3>
                        <p class="text-muted mb-0">Valoración Promedio</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-calendar2-event"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo $estadisticas['eventos_activos']; ?></h3>
                        <p class="text-muted mb-0">Eventos Activos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-tag"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo $estadisticas['promociones_activas']; ?></h3>
                        <p class="text-muted mb-0">Promociones Activas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="row g-4">
            <!-- Reservaciones Próximas -->
            <div class="col-md-6">
                <div class="card activity-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>
                            Próximas Reservaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($reservas_proximas)): ?>
                            <p class="text-muted text-center mb-0">No hay reservaciones próximas</p>
                        <?php else: ?>
                            <?php foreach ($reservas_proximas as $reserva): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($reserva['nombre_usuario_reserva']); ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar2 me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($reserva['fecha_reserva'])); ?> a las 
                                            <?php echo substr($reserva['hora_reserva'], 0, 5); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">
                                        <?php echo $reserva['cantidad_adultos'] + $reserva['cantidad_ninos']; ?> personas
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Opiniones Recientes -->
            <div class="col-md-6">
                <div class="card activity-card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-chat-square-text me-2 text-info"></i>
                            Opiniones Recientes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($opiniones_recientes)): ?>
                            <p class="text-muted text-center mb-0">No hay opiniones recientes</p>
                        <?php else: ?>
                            <?php foreach ($opiniones_recientes as $opinion): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></h6>
                                        <p class="mb-1 small"><?php echo htmlspecialchars(substr($opinion['opinion'], 0, 100)) . '...'; ?></p>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($opinion['fecha_registro_opinion'])); ?>
                                        </small>
                                    </div>
                                    <div class="rating-stars ms-2">
                                        <?php for ($i = 0; $i < $opinion['valoracion']; $i++): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>