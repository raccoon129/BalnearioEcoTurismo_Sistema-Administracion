<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Balneario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .tab-content {
            padding: 20px 0;
        }
        .card {
            margin-bottom: 1rem;
        }
        .social-media-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .horario-badge {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/balneario/mi_balneario/BalnearioController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $balnearioController = new BalnearioController($db);
    $balneario = $balnearioController->obtenerBalneario($_SESSION['id_balneario']);
    $servicios = $balnearioController->obtenerServicios($_SESSION['id_balneario']);
    $reservaciones = $balnearioController->obtenerReservaciones($_SESSION['id_balneario']);
    ?>

    <div class="container py-4">
        <h2 class="mb-4"><i class="bi bi-building me-2"></i>Mi Balneario</h2>

        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#detalles">
                    <i class="bi bi-info-circle me-2"></i>Detalles
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#reservaciones">
                    <i class="bi bi-calendar-check me-2"></i>Reservaciones
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Pestaña de Detalles -->
            <div id="detalles" class="tab-pane fade show active">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="card-title">Información General</h3>
                            <div class="btn-group">
                                <a href="editar.php" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i>Editar Detalles
                                </a>
                                <a href="servicios.php" class="btn btn-success">
                                    <i class="bi bi-gear me-2"></i>Gestionar Servicios
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo htmlspecialchars($balneario['nombre_balneario']); ?></h4>
                                <p class="text-muted">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    <?php echo htmlspecialchars($balneario['direccion_balneario']); ?>
                                </p>
                                <p><?php echo nl2br(htmlspecialchars($balneario['descripcion_balneario'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5><i class="bi bi-clock me-2"></i>Horario</h5>
                                        <p class="horario-badge bg-info text-white d-inline-block">
                                            <?php 
                                            echo date('h:i A', strtotime($balneario['horario_apertura'])) . 
                                                 ' - ' . 
                                                 date('h:i A', strtotime($balneario['horario_cierre'])); 
                                            ?>
                                        </p>
                                        
                                        <h5 class="mt-3"><i class="bi bi-currency-dollar me-2"></i>Precio General</h5>
                                        <p class="horario-badge bg-success text-white d-inline-block">
                                            $<?php echo number_format($balneario['precio_general'], 2); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5><i class="bi bi-envelope me-2"></i>Contacto</h5>
                                <p>
                                    <strong>Email:</strong> <?php echo htmlspecialchars($balneario['email_balneario']); ?><br>
                                    <strong>Teléfono:</strong> <?php echo htmlspecialchars($balneario['telefono_balneario']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="bi bi-share me-2"></i>Redes Sociales</h5>
                                <div class="d-flex align-items-center">
                                    <?php if ($balneario['facebook_balneario']): ?>
                                        <a href="<?php echo htmlspecialchars($balneario['facebook_balneario']); ?>" 
                                           target="_blank" class="text-primary social-media-icon">
                                            <i class="bi bi-facebook"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($balneario['instagram_balneario']): ?>
                                        <a href="<?php echo htmlspecialchars($balneario['instagram_balneario']); ?>" 
                                           target="_blank" class="text-danger social-media-icon">
                                            <i class="bi bi-instagram"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($balneario['x_balneario']): ?>
                                        <a href="<?php echo htmlspecialchars($balneario['x_balneario']); ?>" 
                                           target="_blank" class="text-dark social-media-icon">
                                            <i class="bi bi-twitter-x"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($balneario['tiktok_balneario']): ?>
                                        <a href="<?php echo htmlspecialchars($balneario['tiktok_balneario']); ?>" 
                                           target="_blank" class="text-dark social-media-icon">
                                            <i class="bi bi-tiktok"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Reservaciones -->
            <div id="reservaciones" class="tab-pane fade">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="card-title">Gestión de Reservaciones</h3>
                            <div>
                                <input type="date" id="fechaFiltro" class="form-control" 
                                       value="<?php echo date('Y-m-d'); ?>" 
                                       onchange="filtrarReservaciones(this.value)">
                            </div>
                        </div>

                        <!-- Resumen del día -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Reservaciones</h5>
                                        <h3 id="totalReservaciones">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Adultos</h5>
                                        <h3 id="totalAdultos">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Niños</h5>
                                        <h3 id="totalNinos">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaReservaciones" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Cliente</th>
                                        <th>Contacto</th>
                                        <th>Visitantes</th>
                                        <th>Comentarios</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservaciones as $reservacion): ?>
                                    <tr>
                                        <td><?php echo date('h:i A', strtotime($reservacion['hora_reserva'])); ?></td>
                                        <td><?php echo htmlspecialchars($reservacion['nombre_usuario_reserva']); ?></td>
                                        <td>
                                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($reservacion['telefono_usuario_reserva']); ?><br>
                                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($reservacion['email_usuario_reserva']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $reservacion['cantidad_adultos']; ?> Adultos</span>
                                            <span class="badge bg-info"><?php echo $reservacion['cantidad_ninos']; ?> Niños</span>
                                        </td>
                                        <td><?php echo htmlspecialchars($reservacion['comentarios_reserva']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="verDetallesReservacion(<?php echo $reservacion['id_reservacion']; ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#tablaReservaciones').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[0, 'asc']] // Ordenar por hora
            });

            // Cargar resumen inicial
            actualizarResumen(new Date().toISOString().split('T')[0]);
        });

        function actualizarResumen(fecha) {
            $.get('../../../controllers/balneario/mi_balneario/obtenerResumen.php', {
                fecha: fecha
            })
            .done(function(data) {
                $('#totalReservaciones').text(data.total_reservaciones);
                $('#totalAdultos').text(data.total_adultos);
                $('#totalNinos').text(data.total_ninos);
            });
        }

        function filtrarReservaciones(fecha) {
            $('#tablaReservaciones').DataTable().ajax.reload();
            actualizarResumen(fecha);
        }
    </script>
</body>
</html> 