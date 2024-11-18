<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Opiniones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
    <style>
        .opinion-card {
            transition: transform 0.2s;
            border-radius: 12px;
            overflow: hidden;
        }
        .opinion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        .nav-pills .nav-link {
            color: #6c757d;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .nav-pills .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
        }
        .opinion-image {
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .opinion-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .opinion-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .opinion-card:hover .opinion-actions {
            opacity: 1;
        }
    </style>
</head>

<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/balneario/opiniones/OpinionController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $opinionController = new OpinionController($db);
    $opinionesValidadas = $opinionController->obtenerOpinionesValidadas($_SESSION['id_balneario']);
    $opinionesPendientes = $opinionController->obtenerOpinionesPendientes($_SESSION['id_balneario']);
    $opinionesInvalidadas = $opinionController->obtenerOpinionesInvalidadas($_SESSION['id_balneario']);
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Gestión de Opiniones</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-chat-dots me-2"></i>
                    Administra las opiniones de los usuarios
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="opiniones_invalidadas.php" class="btn btn-outline-secondary">
                    <i class="bi bi-eye-slash me-2"></i>Ver Opiniones Invalidadas
                </a>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Pendientes de Validación</h6>
                        <h2 class="mb-0"><?php echo count($opinionesPendientes); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Opiniones Validadas</h6>
                        <h2 class="mb-0"><?php echo count($opinionesValidadas); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="card-title">Opiniones Invalidadas</h6>
                        <h2 class="mb-0"><?php echo count($opinionesInvalidadas); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegación por pestañas -->
        <ul class="nav nav-pills mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="pill" href="#pendientes">
                    <i class="bi bi-clock-history me-2"></i>Pendientes
                    <span class="badge bg-warning text-dark ms-2"><?php echo count($opinionesPendientes); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#validadas">
                    <i class="bi bi-check-circle me-2"></i>Validadas
                    <span class="badge bg-success ms-2"><?php echo count($opinionesValidadas); ?></span>
                </a>
            </li>
        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content">
            <!-- Pestaña de Opiniones Pendientes -->
            <div id="pendientes" class="tab-pane fade show active">
                <div class="row g-4">
                    <?php foreach ($opinionesPendientes as $opinion): ?>
                    <div class="col-md-6">
                        <div class="card opinion-card h-100">
                            <div class="card-body position-relative">
                                <!-- Acciones rápidas -->
                                <div class="opinion-actions">
                                    <button class="btn btn-sm btn-success me-1" 
                                            onclick="validarOpinion(<?php echo $opinion['id_opinion']; ?>, 1)">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger me-1" 
                                            onclick="validarOpinion(<?php echo $opinion['id_opinion']; ?>, 0)">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <a href="ver.php?id=<?php echo $opinion['id_opinion']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>

                                <!-- Información del usuario -->
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <i class="bi bi-person-circle display-6"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></h5>
                                        <div class="rating-stars">
                                            <?php echo str_repeat('⭐', $opinion['valoracion']); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contenido de la opinión -->
                                <p class="card-text mb-3">
                                    <?php echo nl2br(htmlspecialchars($opinion['opinion'])); ?>
                                </p>

                                <?php if ($opinion['url_foto_opinion']): ?>
                                    <img src="<?php echo htmlspecialchars($opinion['url_foto_opinion']); ?>" 
                                         class="opinion-image w-100 mb-3" alt="Foto de la opinión">
                                <?php endif; ?>

                                <!-- Información de contacto -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="opinion-date">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($opinion['fecha_registro_opinion'])); ?>
                                    </div>
                                    <div>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope me-1"></i>
                                            <?php echo htmlspecialchars($opinion['email_usuario']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($opinionesPendientes)): ?>
                    <div class="col-12">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-square-text display-1"></i>
                            <p class="mt-3">No hay opiniones pendientes de validación</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pestaña de Opiniones Validadas -->
            <div id="validadas" class="tab-pane fade">
                <div class="row g-4">
                    <?php foreach ($opinionesValidadas as $opinion): ?>
                    <div class="col-md-6">
                        <div class="card opinion-card h-100">
                            <div class="card-body position-relative">
                                <!-- Acciones -->
                                <div class="opinion-actions">
                                    <button class="btn btn-sm btn-warning me-1" 
                                            onclick="validarOpinion(<?php echo $opinion['id_opinion']; ?>, 0)">
                                        <i class="bi bi-x-lg"></i>Invalidar
                                    </button>
                                    <a href="ver.php?id=<?php echo $opinion['id_opinion']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>

                                <!-- Información del usuario -->
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <i class="bi bi-person-circle display-6"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></h5>
                                        <div class="rating-stars">
                                            <?php echo str_repeat('⭐', $opinion['valoracion']); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contenido de la opinión -->
                                <p class="card-text mb-3">
                                    <?php echo nl2br(htmlspecialchars($opinion['opinion'])); ?>
                                </p>

                                <?php if ($opinion['url_foto_opinion']): ?>
                                    <img src="<?php echo htmlspecialchars($opinion['url_foto_opinion']); ?>" 
                                         class="opinion-image w-100 mb-3" alt="Foto de la opinión">
                                <?php endif; ?>

                                <!-- Fecha -->
                                <div class="opinion-date">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($opinion['fecha_registro_opinion'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($opinionesValidadas)): ?>
                    <div class="col-12">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-square-text display-1"></i>
                            <p class="mt-3">No hay opiniones validadas</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
    $(document).ready(function() {
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000"
        };
    });

    function validarOpinion(id, validada) {
        const accion = validada ? 'validar' : 'invalidar';
        if (confirm(`¿Está seguro que desea ${accion} esta opinión?`)) {
            $.ajax({
                url: '../../../controllers/balneario/opiniones/validarOpinion.php',
                method: 'POST',
                data: { 
                    id_opinion: id, 
                    validada: validada 
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Error al procesar la solicitud');
                }
            });
        }
    }
    </script>
</body>

</html>