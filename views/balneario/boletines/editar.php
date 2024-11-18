<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Boletín</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
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

    if (!$boletin || $boletin['fecha_envio_boletin'] !== null) {
        header('Location: lista.php?error=' . urlencode('No se puede editar este boletín'));
        exit();
    }
    ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-pencil-square me-2"></i>Editar Boletín</h2>
            <div>
                <button type="button" class="btn btn-danger me-2" onclick="confirmarEliminacion(<?php echo $id_boletin; ?>)">
                    <i class="bi bi-trash me-2"></i>Eliminar
                </button>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="formEditarBoletin">
                    <input type="hidden" name="id_boletin" value="<?php echo $boletin['id_boletin']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Título del Boletín</label>
                        <input type="text" class="form-control" name="titulo" 
                               value="<?php echo htmlspecialchars($boletin['titulo_boletin']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contenido</label>
                        <textarea class="form-control" name="contenido" rows="10" required><?php 
                            echo htmlspecialchars($boletin['contenido_boletin']); 
                        ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/editar_boletin.js"></script>
</body>
</html> 