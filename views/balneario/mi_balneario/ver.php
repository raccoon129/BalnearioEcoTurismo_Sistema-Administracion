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
        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            background: none;
        }
        .nav-tabs .nav-link:hover:not(.active) {
            border-bottom: 3px solid #e9ecef;
        }
        .info-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .social-media-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s;
        }
        .social-media-icon:hover {
            opacity: 0.8;
        }
        .price-badge {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        .schedule-badge {
            background-color: #f8f9fa;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-size: 1.1rem;
            display: inline-block;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
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
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Mi Balneario</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt me-2"></i>
                    <?php echo htmlspecialchars($balneario['direccion_balneario']); ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="editar.php" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>Editar Detalles
                </a>
                <a href="servicios.php" class="btn btn-success">
                    <i class="bi bi-gear me-2"></i>Gestionar Servicios
                </a>
            </div>
        </div>

        <!-- Navegación por pestañas -->
        <ul class="nav nav-tabs mb-4" role="tablist">
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
                <!-- Información General -->
                <div class="info-section">
                    <h4 class="mb-4">Información General</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <h5><?php echo htmlspecialchars($balneario['nombre_balneario']); ?></h5>
                            <p class="text-muted mb-4">
                                <?php echo nl2br(htmlspecialchars($balneario['descripcion_balneario'])); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <!-- Horario -->
                            <div class="mb-4">
                                <h5 class="mb-3">Horario de Atención</h5>
                                <div class="schedule-badge">
                                    <i class="bi bi-clock me-2"></i>
                                    <?php 
                                    echo date('h:i A', strtotime($balneario['horario_apertura'])) . 
                                         ' - ' . 
                                         date('h:i A', strtotime($balneario['horario_cierre'])); 
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Precios -->
                            <div>
                                <h5 class="mb-3">Precios Generales</h5>
                                <div class="d-flex flex-column">
                                    <div class="price-badge bg-success text-white mb-2">
                                        <i class="bi bi-person me-2"></i>
                                        Adultos: $<?php echo number_format($balneario['precio_general_adultos'], 2); ?>
                                    </div>
                                    <div class="price-badge bg-info text-white">
                                        <i class="bi bi-person-heart me-2"></i>
                                        Infantes: $<?php echo number_format($balneario['precio_general_infantes'], 2); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="info-section">
                    <h4 class="mb-4">Información de Contacto</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-3">
                                <i class="bi bi-telephone me-2"></i>
                                <strong>Teléfono:</strong> <?php echo htmlspecialchars($balneario['telefono_balneario']); ?>
                            </p>
                            <p class="mb-3">
                                <i class="bi bi-envelope me-2"></i>
                                <strong>Email:</strong> <?php echo htmlspecialchars($balneario['email_balneario']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Redes Sociales</h5>
                            <div>
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

                <!-- Servicios -->
                <div class="info-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Servicios Disponibles</h4>
                        <a href="servicios.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-gear me-2"></i>Gestionar
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Descripción</th>
                                    <th>Precio Adicional</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $servicio): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($servicio['nombre_servicio']); ?></td>
                                    <td><?php echo htmlspecialchars($servicio['descripcion_servicio']); ?></td>
                                    <td>$<?php echo number_format($servicio['precio_adicional'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($servicios)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        No hay servicios registrados
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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