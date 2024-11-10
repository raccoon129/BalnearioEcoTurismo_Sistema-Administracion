<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida - Panel Administrativo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <?php
    require_once '../../config/database.php';
    require_once '../../controllers/balneario/inicio/EstadisticasController.php';
    
    session_start();
    $database = new Database();
    $db = $database->getConnection();
    
    $estadisticasController = new EstadisticasController($db);
    $id_balneario = $_SESSION['id_balneario'];
    
    // Obtener estadísticas
    $reservas_proximas = $estadisticasController->obtenerReservasProximas($id_balneario);
    $opiniones_recientes = $estadisticasController->obtenerOpinionesRecientes($id_balneario);
    $datos_balneario = $estadisticasController->obtenerDatosBalneario($id_balneario);
    ?>

    <div class="container">
        <!-- Encabezado con información del balneario -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h3 mb-2"><?php echo htmlspecialchars($datos_balneario['nombre_balneario']); ?></h1>
                                <p class="text-muted mb-0">
                                    Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <p class="mb-1">
                                    <i class="bi bi-clock me-2"></i>
                                    Horario: <?php echo substr($datos_balneario['horario_apertura'], 0, 5); ?> - 
                                           <?php echo substr($datos_balneario['horario_cierre'], 0, 5); ?>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-telephone me-2"></i>
                                    <?php echo htmlspecialchars($datos_balneario['telefono_balneario']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row g-4 mb-4">
            <!-- Reservas próximas -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-check me-2"></i>
                            Próximas Reservaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($reservas_proximas)): ?>
                            <p class="text-muted text-center mb-0">No hay reservaciones próximas</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($reservas_proximas as $reserva): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($reserva['nombre_usuario_reserva']); ?></h6>
                                            <small class="text-muted">
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
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Opiniones recientes -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-chat-square-text me-2"></i>
                            Opiniones de la Semana
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($opiniones_recientes)): ?>
                            <p class="text-muted text-center mb-0">No hay opiniones recientes</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($opiniones_recientes as $opinion): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></h6>
                                            <p class="mb-1 small"><?php echo htmlspecialchars(substr($opinion['opinion'], 0, 100)) . '...'; ?></p>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($opinion['fecha_registro_opinion'])); ?>
                                            </small>
                                        </div>
                                        <div class="ms-2">
                                            <?php for ($i = 0; $i < $opinion['valoracion']; $i++): ?>
                                                <i class="bi bi-star-fill text-warning"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>