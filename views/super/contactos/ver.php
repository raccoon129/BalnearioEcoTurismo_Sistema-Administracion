<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Contacto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/contactos/ContactoController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    $id_contacto = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $contactoController = new ContactoController($db);
    $contacto = $contactoController->obtenerContacto($id_contacto);

    if (!$contacto) {
        header('Location: lista.php?error=' . urlencode('Contacto no encontrado'));
        exit();
    }
    ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-person-lines-fill me-2"></i>Detalles del Contacto</h2>
            <div>
                <button type="button" class="btn btn-danger me-2" 
                        onclick="confirmarEliminacion(<?php echo $contacto['id_contacto']; ?>)">
                    <i class="bi bi-trash me-2"></i>Eliminar
                </button>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="card-title mb-4">Información del Contacto</h5>
                        <dl class="row">
                            <dt class="col-sm-4">Nombre:</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($contacto['nombre_usuario_contacto']); ?></dd>

                            <dt class="col-sm-4">Teléfono:</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($contacto['telefono_usuario_contacto']); ?></dd>

                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">
                                <?php echo htmlspecialchars($contacto['email_usuario_contacto'] ?? 'No proporcionado'); ?>
                            </dd>

                            <dt class="col-sm-4">Suscrito:</dt>
                            <dd class="col-sm-8">
                                <?php if ($contacto['suscripcion_boletin']): ?>
                                    <span class="badge bg-success">Sí</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h5 class="card-title mb-4">Mensaje</h5>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($contacto['contenido'] ?? 'Sin mensaje')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/contactos.js"></script>
</body>
</html>
