<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Promociones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
    <style>
        .promo-card {
            transition: transform 0.2s;
            border-radius: 12px;
            overflow: hidden;
        }

        .promo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .promo-date {
            font-size: 0.85rem;
            color: #6c757d;
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

        .promo-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .promo-card:hover .promo-actions {
            opacity: 1;
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

        .card-title a:hover {
            color: #0d6efd !important;
            transition: color 0.2s;
        }

        .promo-actions .btn {
            margin-left: 0.25rem;
        }

        .promo-actions .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }

        .promo-actions .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
    </style>
</head>

<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/balneario/promociones/PromocionController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $promocionController = new PromocionController($db);
    $promociones = $promocionController->obtenerPromociones($_SESSION['id_balneario']);

    // Clasificar promociones
    $promocionesProximas = [];
    $promocionesActivas = [];
    $promocionesFinalizadas = [];
    $hoy = strtotime('today');

    foreach ($promociones as $promocion) {
        $inicio = strtotime($promocion['fecha_inicio_promocion']);
        $fin = strtotime($promocion['fecha_fin_promocion']);

        if ($hoy < $inicio) {
            $promocionesProximas[] = $promocion;
        } elseif ($hoy > $fin) {
            $promocionesFinalizadas[] = $promocion;
        } else {
            $promocionesActivas[] = $promocion;
        }
    }
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Gestión de Promociones</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-tag me-2"></i>
                    Administra las promociones de tu balneario
                </p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPromocion">
                <i class="bi bi-plus-lg me-2"></i>Nueva Promoción
            </button>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-tag-fill"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo count($promocionesActivas); ?></h3>
                        <p class="text-muted mb-0">Promociones Activas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-calendar-plus"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo count($promocionesProximas); ?></h3>
                        <p class="text-muted mb-0">Próximas Promociones</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo count($promocionesFinalizadas); ?></h3>
                        <p class="text-muted mb-0">Promociones Finalizadas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegación por pestañas -->
        <ul class="nav nav-pills mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="pill" href="#activas">
                    <i class="bi bi-tag-fill me-2"></i>Activas
                    <span class="badge bg-primary ms-2"><?php echo count($promocionesActivas); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#proximas">
                    <i class="bi bi-calendar-plus me-2"></i>Próximas
                    <span class="badge bg-success ms-2"><?php echo count($promocionesProximas); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#finalizadas">
                    <i class="bi bi-calendar-x me-2"></i>Finalizadas
                    <span class="badge bg-secondary ms-2"><?php echo count($promocionesFinalizadas); ?></span>
                </a>
            </li>
        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content">
            <!-- Promociones Activas -->
            <div id="activas" class="tab-pane fade show active">
                <div class="row g-4">
                    <?php foreach ($promocionesActivas as $promocion): ?>
                        <div class="col-md-6">
                            <div class="card promo-card h-100">
                                <div class="card-body position-relative">
                                    <!-- Acciones rápidas -->
                                    <div class="promo-actions">
                                        <a href="ver.php?id=<?php echo $promocion['id_promocion']; ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editar.php?id=<?php echo $promocion['id_promocion']; ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="confirmarEliminacion(<?php echo $promocion['id_promocion']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success"
                                            onclick="convertirABoletin(<?php echo $promocion['id_promocion']; ?>)">
                                            <i class="bi bi-envelope"></i>
                                        </button>
                                    </div>

                                    <h5 class="card-title mb-3">
                                        <a href="ver.php?id=<?php echo $promocion['id_promocion']; ?>"
                                            class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($promocion['titulo_promocion']); ?>
                                        </a>
                                    </h5>

                                    <p class="card-text mb-3">
                                        <?php echo nl2br(htmlspecialchars(substr($promocion['descripcion_promocion'], 0, 150))); ?>...
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success status-badge">
                                            <i class="bi bi-tag-fill me-1"></i>En curso
                                        </span>
                                        <div class="promo-date">
                                            <i class="bi bi-calendar-range me-1"></i>
                                            <?php
                                            echo date('d/m/Y', strtotime($promocion['fecha_inicio_promocion'])) .
                                                ' - ' .
                                                date('d/m/Y', strtotime($promocion['fecha_fin_promocion']));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($promocionesActivas)): ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-tag display-1"></i>
                                <p class="mt-3">No hay promociones activas</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Promociones Próximas -->
            <div id="proximas" class="tab-pane fade">
                <div class="row g-4">
                    <?php foreach ($promocionesProximas as $promocion): ?>
                        <div class="col-md-6">
                            <div class="card promo-card h-100">
                                <div class="card-body position-relative">
                                    <!-- Acciones rápidas -->
                                    <div class="promo-actions">
                                        <a href="ver.php?id=<?php echo $promocion['id_promocion']; ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editar.php?id=<?php echo $promocion['id_promocion']; ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="confirmarEliminacion(<?php echo $promocion['id_promocion']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success"
                                            onclick="convertirABoletin(<?php echo $promocion['id_promocion']; ?>)">
                                            <i class="bi bi-envelope"></i>
                                        </button>
                                    </div>

                                    <h5 class="card-title mb-3">
                                        <a href="ver.php?id=<?php echo $promocion['id_promocion']; ?>"
                                            class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($promocion['titulo_promocion']); ?>
                                        </a>
                                    </h5>

                                    <p class="card-text mb-3">
                                        <?php echo nl2br(htmlspecialchars(substr($promocion['descripcion_promocion'], 0, 150))); ?>...
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-info status-badge">
                                            <i class="bi bi-calendar-plus me-1"></i>Próxima
                                        </span>
                                        <div class="promo-date">
                                            <i class="bi bi-calendar-range me-1"></i>
                                            <?php
                                            echo date('d/m/Y', strtotime($promocion['fecha_inicio_promocion'])) .
                                                ' - ' .
                                                date('d/m/Y', strtotime($promocion['fecha_fin_promocion']));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($promocionesProximas)): ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-calendar-plus display-1"></i>
                                <p class="mt-3">No hay promociones próximas</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Promociones Finalizadas -->
            <div id="finalizadas" class="tab-pane fade">
                <div class="row g-4">
                    <?php foreach ($promocionesFinalizadas as $promocion): ?>
                        <div class="col-md-6">
                            <div class="card promo-card h-100">
                                <div class="card-body position-relative">
                                    <!-- Acciones rápidas -->
                                    <div class="promo-actions">
                                        <a href="ver.php?id=<?php echo $promocion['id_promocion']; ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="confirmarEliminacion(<?php echo $promocion['id_promocion']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <h5 class="card-title mb-3">
                                        <a href="ver.php?id=<?php echo $promocion['id_promocion']; ?>"
                                            class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($promocion['titulo_promocion']); ?>
                                        </a>
                                    </h5>

                                    <p class="card-text mb-3">
                                        <?php echo nl2br(htmlspecialchars(substr($promocion['descripcion_promocion'], 0, 150))); ?>...
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-secondary status-badge">
                                            <i class="bi bi-calendar-x me-1"></i>Finalizada
                                        </span>
                                        <div class="promo-date">
                                            <i class="bi bi-calendar-range me-1"></i>
                                            <?php
                                            echo date('d/m/Y', strtotime($promocion['fecha_inicio_promocion'])) .
                                                ' - ' .
                                                date('d/m/Y', strtotime($promocion['fecha_fin_promocion']));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($promocionesFinalizadas)): ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x display-1"></i>
                                <p class="mt-3">No hay promociones finalizadas</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Promoción -->
    <div class="modal fade" id="modalPromocion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Promoción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPromocion">
                        <div class="mb-3">
                            <label class="form-label">Título de la Promoción</label>
                            <input type="text" class="form-control" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="5" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Fecha de Inicio</label>
                                    <input type="date" class="form-control" name="fecha_inicio" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Fecha de Fin</label>
                                    <input type="date" class="form-control" name="fecha_fin" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formPromocion" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar Promoción
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar esta promoción?</p>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" action="../../../controllers/balneario/promociones/eliminar.php" method="POST">
                        <input type="hidden" name="id_promocion" id="id_promocion_eliminar">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar Promoción
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir Modal de Conversión -->
    <?php include 'components/modal_conversion_boletin.php'; ?>
    <!-- Antes de los scripts en lista.php -->
    <script>
        // Información del usuario para uso en JavaScript
        window.userInfo = {
            usuario_id: <?php echo $_SESSION['usuario_id']; ?>,
            nombre_usuario: '<?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>'
        };
    </script>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/convertir_boletin.js"></script>
    <script src="js/promociones.js"></script>
</body>

</html>