<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once '../../../controllers/balneario/opiniones/OpinionController.php';

// Verificar autenticación
session_start();
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$auth->checkAuth();
$auth->checkRole(['administrador_balneario']);

// Obtener datos de la opinión
$id_opinion = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$opinionController = new OpinionController($db);
$opinion = $opinionController->obtenerOpinion($id_opinion);

// Verificar que la opinión existe y pertenece al balneario
if (!$opinion || $opinion['id_balneario'] != $_SESSION['id_balneario']) {
    header('Location: lista.php?error=' . urlencode('Opinión no encontrada'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Opinión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .opinion-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .user-info {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .opinion-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1.5rem;
        }
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .opinion-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-chat-dots me-2"></i>Detalles de la Opinión</h2>
            <a href="lista.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver a la lista
            </a>
        </div>

        <div class="opinion-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-3">Estado de la Opinión</h3>
                    <?php if ($opinion['opinion_validada'] === null): ?>
                        <span class="status-badge bg-warning text-dark">
                            <i class="bi bi-clock-history me-2"></i>Pendiente de Validación
                        </span>
                    <?php elseif ($opinion['opinion_validada'] === 1): ?>
                        <span class="status-badge bg-success text-white">
                            <i class="bi bi-check-circle me-2"></i>Validada
                        </span>
                    <?php else: ?>
                        <span class="status-badge bg-danger text-white">
                            <i class="bi bi-x-circle me-2"></i>Invalidada
                        </span>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="rating-stars">
                        <?php echo str_repeat('⭐', $opinion['valoracion']); ?>
                    </div>
                    <small class="text-muted">
                        <?php echo date('d/m/Y H:i', strtotime($opinion['fecha_registro_opinion'])); ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="user-info">
            <h4><i class="bi bi-person-circle me-2"></i>Información del Usuario</h4>
            <div class="row mt-3">
                <div class="col-md-4">
                    <p><strong>Nombre:</strong><br><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Email:</strong><br><?php echo htmlspecialchars($opinion['email_usuario']); ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Teléfono:</strong><br><?php echo htmlspecialchars($opinion['telefono_usuario']); ?></p>
                </div>
            </div>
        </div>

        <div class="opinion-content">
            <h4 class="mb-4"><i class="bi bi-chat-quote me-2"></i>Opinión</h4>
            <p class="lead">
                <?php echo nl2br(htmlspecialchars($opinion['opinion'])); ?>
            </p>

            <?php if ($opinion['url_foto_opinion']): ?>
                <div class="mt-4">
                    <h5><i class="bi bi-image me-2"></i>Foto adjunta</h5>
                    <img src="<?php echo htmlspecialchars($opinion['url_foto_opinion']); ?>" 
                         alt="Foto de la opinión" class="opinion-image">
                </div>
            <?php endif; ?>
        </div>

        <?php if ($opinion['opinion_validada'] === null): ?>
            <div class="mt-4 d-flex gap-2 justify-content-end">
                <button class="btn btn-success" onclick="validarOpinion(<?php echo $opinion['id_opinion']; ?>, 1)">
                    <i class="bi bi-check-circle me-2"></i>Validar Opinión
                </button>
                <button class="btn btn-danger" onclick="validarOpinion(<?php echo $opinion['id_opinion']; ?>, 0)">
                    <i class="bi bi-x-circle me-2"></i>Invalidar Opinión
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validarOpinion(id, validada) {
            const accion = validada ? 'validar' : 'invalidar';
            if (confirm(`¿Está seguro que desea ${accion} esta opinión?`)) {
                $.ajax({
                    url: '../../../controllers/balneario/opiniones/validarOpinion.php',
                    method: 'POST',
                    data: { id_opinion: id, validada: validada },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'lista.php?success=' + 
                                encodeURIComponent(`Opinión ${validada ? 'validada' : 'invalidada'} exitosamente`);
                        } else {
                            alert('Error: ' + response.error);
                        }
                    }
                });
            }
        }
    </script>
</body>
</html> 