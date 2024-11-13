<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Superadministrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php
    require_once '../../config/database.php';
    require_once '../../config/auth.php';
    require_once '../../controllers/super/inicio/ResumenController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    $resumenController = new ResumenController($db);
    $estadisticas = $resumenController->obtenerEstadisticas();
    $opiniones = $resumenController->obtenerUltimasOpiniones();
    $eventos = $resumenController->obtenerProximosEventos();
    $reservaciones = $resumenController->obtenerUltimasReservaciones();
    ?>

    <div class="container-fluid py-4">
        <h2 class="mb-4">Panel de Control General</h2>

        <!-- Tarjetas de Estadísticas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Balnearios Registrados</h5>
                        <h2><?php echo $estadisticas['total_balnearios']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Opiniones esta Semana</h5>
                        <h2><?php echo $estadisticas['opiniones_semana']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Reservaciones esta Semana</h5>
                        <h2><?php echo $estadisticas['reservaciones_semana']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Eventos Activos</h5>
                        <h2><?php echo $estadisticas['eventos_activos']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimas Actividades -->
        <div class="row">
            <!-- Últimas Opiniones -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Últimas Opiniones</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($opiniones as $opinion): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($opinion['nombre_balneario']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($opinion['fecha_registro_opinion'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($opinion['opinion']); ?></p>
                                    <small>
                                        Valoración:
                                        <?php for ($i = 0; $i < $opinion['valoracion']; $i++): ?>
                                            <i class="bi bi-star-fill text-warning"></i>
                                        <?php endfor; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Próximos Eventos -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Próximos Eventos</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($eventos as $evento): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($evento['titulo_evento']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($evento['fecha_inicio_evento'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($evento['nombre_balneario']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimas Reservaciones -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Últimas Reservaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Balneario</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Visitantes</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservaciones as $reservacion): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($reservacion['nombre_balneario']); ?></td>
                                            <td><?php echo htmlspecialchars($reservacion['nombre_usuario_reserva']); ?></td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($reservacion['fecha_reserva'])); ?>
                                                <small class="text-muted">
                                                    <?php echo date('h:i A', strtotime($reservacion['hora_reserva'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo $reservacion['cantidad_adultos']; ?> Adultos
                                                </span>
                                                <span class="badge bg-info">
                                                    <?php echo $reservacion['cantidad_ninos']; ?> Niños
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (strtotime($reservacion['fecha_reserva']) < strtotime('today')): ?>
                                                    <span class="badge bg-secondary">Finalizada</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Próxima</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <br>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <br>
        </div>
        <br>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>