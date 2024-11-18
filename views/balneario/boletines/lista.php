<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Boletines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
    <style>
        .boletin-card {
            transition: transform 0.2s;
            border-radius: 12px;
            overflow: hidden;
        }

        .boletin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .estado-borrador {
            background-color: #ffc107;
            color: #000;
        }

        .estado-enviado {
            background-color: #198754;
            color: #fff;
        }

        .estado-badge {
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
    </style>
</head>

<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/balneario/boletines/BoletinController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $boletinController = new BoletinController($db);
    $boletines = $boletinController->obtenerBoletines($_SESSION['id_balneario']);

    // Clasificar boletines
    $borradores = array_filter($boletines, function ($boletin) {
        return $boletin['fecha_envio_boletin'] === null;
    });

    $enviados = array_filter($boletines, function ($boletin) {
        return $boletin['fecha_envio_boletin'] !== null;
    });

    // Obtener estadísticas
    $estadisticas = $boletinController->obtenerEstadisticas($_SESSION['id_balneario']);
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Gestión de Boletines</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-envelope me-2"></i>
                    Administra y envía boletines a tus suscriptores
                </p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBoletin">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Boletín
            </button>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-envelope-paper"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo count($boletines); ?></h3>
                        <p class="text-muted mb-0">Total de Boletines</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo count($borradores); ?></h3>
                        <p class="text-muted mb-0">Borradores</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-send-check"></i>
                        </div>
                        <h3 class="fw-bold mb-2"><?php echo count($enviados); ?></h3>
                        <p class="text-muted mb-0">Boletines Enviados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navegación por pestañas -->
        <ul class="nav nav-pills mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="pill" href="#borradores">
                    <i class="bi bi-file-earmark-text me-2"></i>Borradores
                    <span class="badge bg-warning text-dark ms-2"><?php echo count($borradores); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#enviados">
                    <i class="bi bi-send-check me-2"></i>Enviados
                    <span class="badge bg-success ms-2"><?php echo count($enviados); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#suscriptores">
                    <i class="bi bi-people me-2"></i>Suscriptores
                    <span class="badge bg-info ms-2"><?php echo $estadisticas['total_suscriptores']; ?></span>
                </a>
            </li>
        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content">
            <!-- Pestaña de Borradores -->
            <div id="borradores" class="tab-pane fade show active">
                <div class="row g-4">
                    <?php foreach ($borradores as $boletin): ?>
                        <div class="col-md-6">
                            <div class="card boletin-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title"><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></h5>
                                        <span class="badge estado-borrador estado-badge">
                                            <i class="bi bi-file-earmark me-1"></i>Borrador
                                        </span>
                                    </div>

                                    <p class="card-text mb-3">
                                        <?php echo nl2br(htmlspecialchars(substr($boletin['contenido_boletin'], 0, 150))); ?>...
                                    </p>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-success"
                                            onclick="enviarBoletin(<?php echo $boletin['id_boletin']; ?>)">
                                            <i class="bi bi-send me-1"></i>Enviar
                                        </button>
                                        <a href="editar.php?id=<?php echo $boletin['id_boletin']; ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil me-1"></i>Editar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($borradores)): ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-file-earmark-text display-1"></i>
                                <p class="mt-3">No hay borradores de boletines</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pestaña de Enviados -->
            <div id="enviados" class="tab-pane fade">
                <div class="row g-4">
                    <?php foreach ($enviados as $boletin): ?>
                        <div class="col-md-6">
                            <div class="card boletin-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title"><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></h5>
                                        <span class="badge estado-enviado estado-badge">
                                            <i class="bi bi-send-check me-1"></i>Enviado
                                        </span>
                                    </div>

                                    <p class="card-text mb-3">
                                        <?php echo nl2br(htmlspecialchars(substr($boletin['contenido_boletin'], 0, 150))); ?>...
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar2 me-1"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($boletin['fecha_envio_boletin'])); ?>
                                        </small>
                                        <a href="detalles.php?id=<?php echo $boletin['id_boletin']; ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Ver Detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($enviados)): ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-send display-1"></i>
                                <p class="mt-3">No hay boletines enviados</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pestaña de Suscriptores -->
            <div id="suscriptores" class="tab-pane fade">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaSuscriptores" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Fecha de Suscripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $suscriptores = $boletinController->obtenerSuscriptores($_SESSION['id_balneario']);
                                    foreach ($suscriptores as $suscriptor):
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($suscriptor['nombre_usuario']); ?></td>
                                            <td><?php echo htmlspecialchars($suscriptor['email_usuario']); ?></td>
                                            <td><?php echo htmlspecialchars($suscriptor['telefono_usuario'] ?? 'No proporcionado'); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($suscriptor['fecha_registro_opinion'])); ?></td>
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

    <!-- Modal para Nuevo Boletín -->
    <div class="modal fade" id="modalBoletin" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Boletín</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formBoletin" action="../../../controllers/balneario/boletines/guardar.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Título del Boletín</label>
                            <input type="text" class="form-control" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contenido</label>
                            <textarea class="form-control" name="contenido" rows="10" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formBoletin" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar como Borrador
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Información del usuario para uso en JavaScript
        window.userInfo = {
            id_usuario: <?php echo $_SESSION['id_usuario']; ?>,
            nombre_usuario: '<?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>'
        };
    </script>

    <!-- Modal de Envío de Boletín -->
    <?php include 'components/modal_envio_boletin.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/boletines.js"></script>
</body>

</html>