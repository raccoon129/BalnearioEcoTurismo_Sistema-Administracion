<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Boletín</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .estado-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .estado-borrador { background-color: #ffc107; color: black; }
        .estado-enviado { background-color: #198754; color: white; }
        .contenido-boletin {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
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

    $id_boletin = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());

    $boletin = $boletinController->obtenerDetallesBoletinSuperAdmin($id_boletin);

    if (!$boletin) {
        header('Location: lista.php');
        exit();
    }
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Detalles del Boletín</h2>
            </div>
            <div class="d-flex gap-2">
                <?php if ($boletin['estado_boletin'] === 'borrador'): ?>
                    <button type="button" class="btn btn-success" onclick="enviarBoletin(<?php echo $id_boletin; ?>)">
                        <i class="bi bi-send me-2"></i>Enviar Boletín
                    </button>
                <?php endif; ?>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <!-- Información del Boletín -->
        <div class="row">
            <div class="col-md-8">
                <!-- Contenido del Boletín -->
                <div class="contenido-boletin mb-4">
                    <h3><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></h3>
                    <hr>
                    <div class="boletin-contenido">
                        <?php echo nl2br(htmlspecialchars($boletin['contenido_boletin'])); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- Detalles y Estado -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Información del Boletín</h5>
                        
                        <div class="mb-3 text-center">
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
                                Creado: <?php echo date('d/m/Y H:i', strtotime($boletin['fecha_creacion'])); ?>
                            </small>
                            <?php if ($boletin['estado_boletin'] === 'enviado'): ?>
                                <small class="text-muted d-block">
                                    <i class="bi bi-send me-2"></i>
                                    Enviado: <?php echo date('d/m/Y H:i', strtotime($boletin['fecha_envio'])); ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <?php if ($boletin['estado_boletin'] === 'borrador'): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle me-2"></i>
                                Este boletín está en borrador y aún no ha sido enviado.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/boletines.js"></script>
</body>
</html> 