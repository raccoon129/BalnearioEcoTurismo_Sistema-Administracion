<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once '../../../controllers/balneario/eventos/EventoController.php';

// Verificar autenticación
session_start();
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$auth->checkAuth();
$auth->checkRole(['administrador_balneario']);

// Obtener datos del evento
$id_evento = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$eventoController = new EventoController($db);
$evento = $eventoController->obtenerEvento($id_evento);

// Verificar que el evento existe y pertenece al balneario
if (!$evento || $evento['id_balneario'] != $_SESSION['id_balneario']) {
    header('Location: lista.php?error=' . urlencode('Evento no encontrado'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Evento</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-calendar-event me-2"></i>Detalles del Evento</h2>
            <div>
                <a href="editar.php?id=<?php echo $evento['id_evento']; ?>" class="btn btn-primary me-2">
                    <i class="bi bi-pencil me-2"></i>Editar
                </a>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <?php if ($evento['url_imagen_evento']): ?>
                    <div class="col-md-4 mb-4 mb-md-0">
                        <img src="../../<?php echo htmlspecialchars($evento['url_imagen_evento']); ?>" 
                             class="img-fluid rounded" alt="Imagen del evento">
                    </div>
                    <?php endif; ?>

                    <div class="<?php echo $evento['url_imagen_evento'] ? 'col-md-8' : 'col-12'; ?>">
                        <h3 class="h4 mb-3"><?php echo htmlspecialchars($evento['titulo_evento']); ?></h3>
                        
                        <div class="mb-4">
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

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Fecha de Inicio</h6>
                                <p class="mb-3">
                                    <i class="bi bi-calendar me-2"></i>
                                    <?php echo date('d/m/Y', strtotime($evento['fecha_inicio_evento'])); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Fecha de Fin</h6>
                                <p class="mb-3">
                                    <i class="bi bi-calendar me-2"></i>
                                    <?php echo date('d/m/Y', strtotime($evento['fecha_fin_evento'])); ?>
                                </p>
                            </div>
                        </div>

                        <h6 class="text-muted mb-2">Descripción</h6>
                        <div class="bg-light p-3 rounded">
                            <?php echo nl2br(htmlspecialchars($evento['descripcion_evento'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>