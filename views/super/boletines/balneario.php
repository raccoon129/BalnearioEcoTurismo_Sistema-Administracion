<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletines del Balneario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .boletin-card {
            transition: all 0.3s ease;
        }
        .boletin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .estado-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        .estado-borrador { background-color: #ffc107; color: black; }
        .estado-enviado { background-color: #198754; color: white; }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/boletines/BoletinSuperController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Obtener ID del balneario
    $id_balneario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id_balneario) {
        header('Location: lista.php');
        exit();
    }

    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());
    $balneario = $boletinController->obtenerBalneario($id_balneario);
    if (!$balneario) {
        header('Location: lista.php');
        exit();
    }

    // Obtener estadísticas y boletines del balneario
    $estadisticas = $boletinController->obtenerEstadisticasBalneario($id_balneario);
    $boletines = $boletinController->obtenerTodosBoletinesBalnearios(['id_balneario' => $id_balneario]);
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>
                    <i class="bi bi-water me-2"></i>
                    <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                </h2>
                <p class="text-muted mb-0">Gestión de Boletines</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBoletin">
                    <i class="bi bi-plus-lg me-2"></i>Nuevo Boletín
                </button>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-envelope me-2"></i>Total de Boletines
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['total_boletines']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-file-earmark me-2"></i>Borradores
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['borradores']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-send me-2"></i>Enviados
                        </h6>
                        <h2 class="mb-0"><?php echo $estadisticas['enviados']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filtrosBoletines" class="row g-3">
                    <input type="hidden" name="id" value="<?php echo $id_balneario; ?>">
                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado">
                            <option value="">Todos los estados</option>
                            <option value="borrador">Borradores</option>
                            <option value="enviado">Enviados</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
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

        <!-- Lista de boletines -->
        <div class="row g-4">
            <?php foreach ($boletines as $boletin): ?>
            <div class="col-md-6">
                <div class="card boletin-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title"><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></h5>
                            <span class="badge estado-<?php echo $boletin['estado_boletin']; ?> estado-badge">
                                <?php if ($boletin['estado_boletin'] === 'borrador'): ?>
                                    <i class="bi bi-file-earmark me-1"></i>Borrador
                                <?php else: ?>
                                    <i class="bi bi-send me-1"></i>Enviado
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">
                                <i class="bi bi-person me-2"></i>
                                Creado por: <?php echo htmlspecialchars($boletin['nombre_usuario']); ?>
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-calendar2 me-2"></i>
                                <?php echo date('d/m/Y H:i', strtotime($boletin['fecha_envio_boletin'] ?? $boletin['fecha_envio_boletin'])); ?>
                            </small>
                        </div>

                        <p class="card-text">
                            <?php echo nl2br(htmlspecialchars(substr($boletin['contenido_boletin'], 0, 200))); ?>...
                        </p>

                        <div class="d-flex justify-content-end gap-2">
                            <?php if ($boletin['estado_boletin'] === 'borrador'): ?>
                                <button type="button" class="btn btn-sm btn-success" 
                                        onclick="enviarBoletin(<?php echo $boletin['id_boletin']; ?>)">
                                    <i class="bi bi-send me-1"></i>Enviar
                                </button>
                                <a href="detalles.php?id=<?php echo $boletin['id_boletin']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil me-1"></i>Editar
                                </a>
                            <?php else: ?>
                                <a href="detalles.php?id=<?php echo $boletin['id_boletin']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Ver Detalles
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($boletines)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-envelope-x display-1"></i>
                    <p class="mt-3">No se encontraron boletines para este balneario</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para Nuevo Boletín -->
    <?php include 'components/modal_boletin.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/boletines.js"></script>
</body>
</html> 