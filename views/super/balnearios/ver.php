<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Balneario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Estilos para las tarjetas de información */
        .tarjeta-info {
            transition: transform 0.2s;
            border-radius: 10px;
            overflow: hidden;
        }
        .tarjeta-info:hover {
            transform: translateY(-5px);
        }

        /* Estilos para las pestañas */
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 1rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
        }
        .nav-tabs .nav-link:hover {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
        }

        /* Estilos para los badges de estado */
        .estado-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }

        /* Estilos para las redes sociales */
        .red-social-link {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: #6c757d;
            transition: color 0.3s ease;
        }
        .red-social-link:hover {
            color: #0d6efd;
        }

        /* Estilos para la tarjeta de promoción */
        .tarjeta-promocion {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .tarjeta-promocion:hover {
            transform: translateY(-5px);
        }

        /* Estilos para el avatar inicial */
        .avatar-inicial {
            width: 35px;
            height: 35px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #495057;
        }

        /* Añadir estos estilos */
        .dataTables_wrapper {
            width: 100%;
        }

        .dataTables_wrapper .row {
            width: 100%;
            margin: 0;
        }

        table.dataTable {
            width: 100% !important;
        }

        .table-responsive {
            padding: 0;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    // Importación de dependencias necesarias
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/balnearios/BalnearioSuperController.php';

    // Inicialización de la sesión y verificación de autenticación
    session_start();
    $baseDatos = new Database();
    $conexion = $baseDatos->getConnection();
    $autenticacion = new Auth($conexion);

    $autenticacion->checkAuth();
    $autenticacion->checkRole(['superadministrador']);

    // Obtención del ID del balneario desde la URL
    $idBalneario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Obtención de los detalles del balneario
    $controladorBalneario = new BalnearioSuperController($conexion);
    $balneario = $controladorBalneario->obtenerDetallesBalneario($idBalneario);

    // Verificación de existencia del balneario
    if (!$balneario) {
        header('Location: lista.php?error=' . urlencode('Balneario no encontrado'));
        exit();
    }
    ?>

    <div class="container-fluid py-4">
        <!-- Encabezado con información principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><?php echo htmlspecialchars($balneario['nombre_balneario']); ?></h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt me-2"></i>
                    <?php echo htmlspecialchars($balneario['direccion_balneario']); ?>
                </p>
            </div>
            <a href="editar.php?id=<?php echo $idBalneario; ?>" class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>Editar Balneario
            </a>
        </div>

        <!-- Tarjetas de estadísticas rápidas -->
        <div class="row g-4 mb-4">
            <!-- Tarjeta de Usuarios -->
            <div class="col-md-3">
                <div class="card tarjeta-info bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-people me-2"></i>Usuarios Registrados
                        </h6>
                        <h2 class="mb-0"><?php echo count($balneario['usuarios']); ?></h2>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta de Eventos -->
            <div class="col-md-3">
                <div class="card tarjeta-info bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-calendar-event me-2"></i>Eventos Activos
                        </h6>
                        <h2 class="mb-0"><?php echo count($balneario['eventos']); ?></h2>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta de Promociones -->
            <div class="col-md-3">
                <div class="card tarjeta-info bg-warning text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-tag me-2"></i>Promociones Activas
                        </h6>
                        <h2 class="mb-0"><?php echo count($balneario['promociones']); ?></h2>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta de Valoración -->
            <div class="col-md-3">
                <div class="card tarjeta-info bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-star me-2"></i>Valoración Promedio
                        </h6>
                        <h2 class="mb-0">
                            <?php echo number_format($balneario['estadisticas_opiniones']['valoracion_promedio'], 1); ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegación por pestañas -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#informacion">
                    <i class="bi bi-info-circle me-2"></i>Información General
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#servicios">
                    <i class="bi bi-gear me-2"></i>Servicios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#eventos">
                    <i class="bi bi-calendar me-2"></i>Eventos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#promociones">
                    <i class="bi bi-tag me-2"></i>Promociones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#usuarios">
                    <i class="bi bi-people me-2"></i>Usuarios
                </a>
            </li>
        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content">
            <!-- Pestaña de Información General -->
            <div class="tab-pane fade show active" id="informacion">
                <div class="row">
                    <!-- Información Básica -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Información Básica</h5>
                                
                                <!-- Descripción del balneario -->
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Descripción</h6>
                                    <p><?php echo nl2br(htmlspecialchars($balneario['descripcion_balneario'])); ?></p>
                                </div>

                                <!-- Horarios y Precios -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Horario de Atención</h6>
                                        <p class="mb-0">
                                            <i class="bi bi-clock me-2"></i>
                                            <?php 
                                            echo date('h:i A', strtotime($balneario['horario_apertura'])) . 
                                                 ' - ' . 
                                                 date('h:i A', strtotime($balneario['horario_cierre'])); 
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Precios Generales</h6>
                                        <p class="mb-1">
                                            <i class="bi bi-person me-2"></i>
                                            <strong>Adultos:</strong> $<?php echo number_format($balneario['precio_general_adultos'], 2); ?>
                                        </p>
                                        <p class="mb-0">
                                            <i class="bi bi-person-heart me-2"></i>
                                            <strong>Infantes:</strong> $<?php echo number_format($balneario['precio_general_infantes'], 2); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Información de Contacto -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Teléfono</h6>
                                        <p class="mb-3">
                                            <i class="bi bi-telephone me-2"></i>
                                            <?php echo htmlspecialchars($balneario['telefono_balneario']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Correo Electrónico</h6>
                                        <p class="mb-3">
                                            <i class="bi bi-envelope me-2"></i>
                                            <?php echo htmlspecialchars($balneario['email_balneario']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Redes Sociales y Estadísticas -->
                    <div class="col-md-4">
                        <!-- Redes Sociales -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Redes Sociales</h5>
                                <div class="d-flex flex-wrap">
                                    <?php if ($balneario['facebook_balneario']): ?>
                                        <a href="<?php echo htmlspecialchars($balneario['facebook_balneario']); ?>" 
                                           target="_blank" class="red-social-link">
                                            <i class="bi bi-facebook"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($balneario['instagram_balneario']): ?>
                                        <a href="<?php echo htmlspecialchars($balneario['instagram_balneario']); ?>" 
                                           target="_blank" class="red-social-link">
                                            <i class="bi bi-instagram"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($balneario['x_balneario']): ?>
                                        <a href="<?php echo htmlspecialchars($balneario['x_balneario']); ?>" 
                                           target="_blank" class="red-social-link">
                                            <i class="bi bi-twitter-x"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($balneario['tiktok_balneario']): ?>
                                        <a href="<?php echo htmlspecialchars($balneario['tiktok_balneario']); ?>" 
                                           target="_blank" class="red-social-link">
                                            <i class="bi bi-tiktok"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas de Opiniones -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Estadísticas de Opiniones</h5>
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Total de Opiniones</h6>
                                    <p class="h3 mb-0"><?php echo $balneario['estadisticas_opiniones']['total_opiniones']; ?></p>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Opiniones Validadas</h6>
                                    <p class="h3 mb-0"><?php echo $balneario['estadisticas_opiniones']['opiniones_validadas']; ?></p>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-2">Valoración Promedio</h6>
                                    <div class="h3">
                                        <?php 
                                        $valoracionPromedio = $balneario['estadisticas_opiniones']['valoracion_promedio'];
                                        for ($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <i class="bi bi-star<?php echo $i <= $valoracionPromedio ? '-fill' : ''; ?> text-warning"></i>
                                        <?php endfor; ?>
                                        <span class="ms-2">
                                            <?php echo number_format($valoracionPromedio, 1); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        
                    </div>
                </div>
            </div>

            <!-- Pestaña de Servicios -->
            <div class="tab-pane fade" id="servicios">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Servicios Disponibles</h5>
                        
                        <!-- Tabla de servicios con diseño mejorado -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaServicios">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Descripción</th>
                                        <th>Precio Adicional</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($balneario['servicios'] as $servicio): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($servicio['nombre_servicio']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($servicio['descripcion_servicio']); ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                $<?php echo number_format($servicio['precio_adicional'], 2); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">Activo</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Eventos -->
            <div class="tab-pane fade" id="eventos">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Eventos Programados</h5>
                        
                        <!-- Grid de eventos -->
                        <div class="row g-4">
                            <?php foreach ($balneario['eventos'] as $evento): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    <?php if ($evento['url_imagen_evento']): ?>
                                    <img src="<?php echo htmlspecialchars($evento['url_imagen_evento']); ?>" 
                                         class="card-img-top" alt="Imagen del evento"
                                         style="height: 200px; object-fit: cover;">
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($evento['titulo_evento']); ?></h5>
                                        
                                        <p class="card-text">
                                            <?php echo htmlspecialchars(substr($evento['descripcion_evento'], 0, 150)) . '...'; ?>
                                        </p>
                                        
                                        <!-- Fechas del evento -->
                                        <div class="mb-3">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-calendar-event me-2"></i>
                                                Inicio: <?php echo date('d/m/Y', strtotime($evento['fecha_inicio_evento'])); ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-calendar-check me-2"></i>
                                                Fin: <?php echo date('d/m/Y', strtotime($evento['fecha_fin_evento'])); ?>
                                            </small>
                                        </div>

                                        <!-- Estado del evento -->
                                        <?php
                                        $hoy = strtotime('today');
                                        $inicio = strtotime($evento['fecha_inicio_evento']);
                                        $fin = strtotime($evento['fecha_fin_evento']);
                                        
                                        if ($hoy < $inicio):
                                        ?>
                                            <span class="badge bg-info">Próximo</span>
                                        <?php elseif ($hoy > $fin): ?>
                                            <span class="badge bg-secondary">Finalizado</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">En curso</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Mensaje si no hay eventos -->
                        <?php if (empty($balneario['eventos'])): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x display-1"></i>
                            <p class="mt-3">No hay eventos programados actualmente</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Promociones -->
            <div class="tab-pane fade" id="promociones">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Promociones Activas</h5>
                        
                        <!-- Grid de promociones -->
                        <div class="row g-4">
                            <?php foreach ($balneario['promociones'] as $promocion): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 tarjeta-promocion">
                                    <div class="card-body">
                                        <!-- Título de la promoción -->
                                        <h5 class="card-title text-primary">
                                            <?php echo htmlspecialchars($promocion['titulo_promocion']); ?>
                                        </h5>
                                        
                                        <!-- Descripción de la promoción -->
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($promocion['descripcion_promocion']); ?>
                                        </p>
                                        
                                        <!-- Fechas de validez -->
                                        <div class="mb-3">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-calendar-event me-2"></i>
                                                Inicia: <?php echo date('d/m/Y', strtotime($promocion['fecha_inicio_promocion'])); ?>
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-calendar-check me-2"></i>
                                                Termina: <?php echo date('d/m/Y', strtotime($promocion['fecha_fin_promocion'])); ?>
                                            </small>
                                        </div>

                                        <!-- Estado de la promoción -->
                                        <?php
                                        $hoy = strtotime('today');
                                        $inicio = strtotime($promocion['fecha_inicio_promocion']);
                                        $fin = strtotime($promocion['fecha_fin_promocion']);
                                        
                                        if ($hoy < $inicio):
                                        ?>
                                            <span class="badge bg-info estado-badge">
                                                <i class="bi bi-clock me-1"></i>Próxima
                                            </span>
                                        <?php elseif ($hoy > $fin): ?>
                                            <span class="badge bg-secondary estado-badge">
                                                <i class="bi bi-check-circle me-1"></i>Finalizada
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success estado-badge">
                                                <i class="bi bi-star me-1"></i>Activa
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Mensaje si no hay promociones -->
                        <?php if (empty($balneario['promociones'])): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-tag-x display-1"></i>
                            <p class="mt-3">No hay promociones activas actualmente</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Pestaña de Usuarios -->
            <div class="tab-pane fade" id="usuarios">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Usuarios Registrados</h5>
                        
                        <!-- Tabla de usuarios -->
                        <div class="table-responsive">
                            <table class="table table-hover w-100" id="tablaUsuarios">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Correo Electrónico</th>
                                        <th>Rol</th>
                                        <th>Fecha de Registro</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($balneario['usuarios'] as $usuario): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-inicial me-2">
                                                    <?php echo strtoupper(substr($usuario['nombre_usuario'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="bi bi-envelope me-2 text-muted"></i>
                                            <?php echo htmlspecialchars($usuario['email_usuario']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php 
                                                echo (isset($usuario['rol_usuario']) && $usuario['rol_usuario'] === 'administrador_balneario')
                                                    ? 'Administrador de balneario' 
                                                    : 'SuperAdministrador'; 
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="bi bi-calendar me-2 text-muted"></i>
                                            <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Activo</span>
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

    <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializar DataTable para la tabla de servicios
        $('#tablaServicios').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[0, 'asc']], // Ordenar por nombre de servicio
            responsive: true
        });

        // Inicializar DataTable para la tabla de usuarios
        $('#tablaUsuarios').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[3, 'desc']], // Ordenar por fecha de registro
            responsive: true,
            autoWidth: false,
            columnDefs: [
                { targets: 0, width: '25%' }, // Nombre
                { targets: 1, width: '30%' }, // Email
                { targets: 2, width: '15%' }, // Rol
                { targets: 3, width: '20%' }, // Fecha
                { targets: 4, width: '10%' }  // Estado
            ],
            scrollX: true
        });

        // Activar tooltips de Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html> 