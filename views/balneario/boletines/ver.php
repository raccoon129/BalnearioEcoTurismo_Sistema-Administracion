<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Boletín</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .boletin-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .boletin-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
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

    // Obtener datos del boletín
    $id_boletin = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $boletinController = new BoletinController($db);
    $boletin = $boletinController->obtenerBoletin($id_boletin, $_SESSION['id_balneario']);

    if (!$boletin) {
        header('Location: lista.php?error=' . urlencode('Boletín no encontrado'));
        exit();
    }
    ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-envelope me-2"></i>Detalles del Boletín</h2>
            <a href="lista.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver a la lista
            </a>
        </div>

        <div class="boletin-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-3">Estado del Boletín</h3>
                    <?php if ($boletin['fecha_envio_boletin'] === null): ?>
                        <span class="status-badge bg-warning text-dark">
                            <i class="bi bi-clock-history me-2"></i>Borrador
                        </span>
                    <?php else: ?>
                        <span class="status-badge bg-success text-white">
                            <i class="bi bi-check-circle me-2"></i>Enviado
                        </span>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <small class="text-muted">
                        <?php 
                        if ($boletin['fecha_envio_boletin']) {
                            echo "Enviado el: " . date('d/m/Y H:i', strtotime($boletin['fecha_envio_boletin']));
                        }
                        ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="boletin-content">
            <h4 class="mb-4"><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></h4>
            <div class="boletin-body">
                <?php echo nl2br(htmlspecialchars($boletin['contenido_boletin'])); ?>
            </div>

            <?php if ($boletin['fecha_envio_boletin'] === null): ?>
                <div class="mt-4">
                    <form action="../../../controllers/balneario/boletines/enviar.php" method="POST" 
                          onsubmit="return confirm('¿Está seguro que desea enviar este boletín?')">
                        <input type="hidden" name="id_boletin" value="<?php echo $boletin['id_boletin']; ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send me-2"></i>Enviar Boletín
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 