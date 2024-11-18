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

// Al inicio del archivo, después de los requires
define('BASE_URL', '/administracionBalnearioEcoTurismo/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .event-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .event-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 100%;
        }
        .event-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-top: 1.5rem;
        }
        .event-status {
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 1rem;
        }
        .status-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .status-text {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .date-badge {
            font-size: 1.1rem;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: inline-block;
            background-color: #f8f9fa;
        }
        .no-image-placeholder {
            background-color: #f8f9fa;
            padding: 3rem;
            border-radius: 12px;
            text-align: center;
            margin-top: 1.5rem;
        }
        .no-image-icon {
            font-size: 4rem;
            color: #adb5bd;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    $hoy = strtotime('today');
    $inicio = strtotime($evento['fecha_inicio_evento']);
    $fin = strtotime($evento['fecha_fin_evento']);
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Detalles del Evento</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-calendar-event me-2"></i>
                    <?php echo htmlspecialchars($evento['titulo_evento']); ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <?php if ($hoy <= $fin): ?>
                    <button class="btn btn-success" onclick="convertirABoletin(<?php echo $evento['id_evento']; ?>)">
                        <i class="bi bi-envelope me-2"></i>Convertir a Boletín
                    </button>
                <?php endif; ?>
                <a href="editar.php?id=<?php echo $evento['id_evento']; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>Editar
                </a>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Columna Izquierda: Descripción e Imagen -->
            <div class="col-md-8">
                <div class="event-content">
                    <h4 class="mb-4">Descripción del Evento</h4>
                    <p class="lead">
                        <?php echo nl2br(htmlspecialchars($evento['descripcion_evento'])); ?>
                    </p>

                    <?php if ($evento['url_imagen_evento']): ?>
                        <img src="<?php echo BASE_URL . htmlspecialchars($evento['url_imagen_evento']); ?>" 
                             alt="Imagen del evento" class="event-image">
                    <?php else: ?>
                        <div class="no-image-placeholder">
                            <i class="bi bi-image no-image-icon"></i>
                            <p class="text-muted mb-0">No existe imagen del evento</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna Derecha: Estado y Fechas -->
            <div class="col-md-4">
                <div class="event-content">
                    <?php if ($hoy < $inicio): ?>
                        <div class="event-status bg-info bg-opacity-10">
                            <i class="bi bi-calendar-plus status-icon text-info"></i>
                            <p class="status-text text-info">Próximo</p>
                            <p class="text-muted mb-0">El evento aún no ha comenzado</p>
                        </div>
                    <?php elseif ($hoy > $fin): ?>
                        <div class="event-status bg-secondary bg-opacity-10">
                            <i class="bi bi-calendar-x status-icon text-secondary"></i>
                            <p class="status-text text-secondary">Finalizado</p>
                            <p class="text-muted mb-0">El evento ha terminado</p>
                        </div>
                    <?php else: ?>
                        <div class="event-status bg-success bg-opacity-10">
                            <i class="bi bi-calendar-check status-icon text-success"></i>
                            <p class="status-text text-success">En Curso</p>
                            <p class="text-muted mb-0">El evento está activo</p>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <h5 class="mb-3">Fechas del Evento</h5>
                        <div class="date-badge mb-2">
                            <i class="bi bi-calendar-event me-2"></i>
                            <strong>Inicio:</strong><br>
                            <?php echo date('d/m/Y', strtotime($evento['fecha_inicio_evento'])); ?>
                        </div>
                        <div class="date-badge">
                            <i class="bi bi-calendar-event me-2"></i>
                            <strong>Fin:</strong><br>
                            <?php echo date('d/m/Y', strtotime($evento['fecha_fin_evento'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir Modal de Conversión a Boletín -->
    <?php include 'components/modal_conversion_boletin.php'; ?>

    <!-- Modal de Conversión -->
    <div class="modal fade" id="modalConfirmarConversion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Convertir a Boletín</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Desea crear un boletín con la información de este evento?</p>
                    <p>Se creará un borrador que podrá revisar antes de enviarlo.</p>
                    <div id="previewContenido" class="mt-3 p-3 bg-light rounded">
                        <!-- Aquí se mostrará la vista previa del contenido -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnConfirmarConversion">
                        <i class="bi bi-envelope me-2"></i>Crear Boletín
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
    // Almacenar los datos del evento para uso global
    window.eventoActual = <?php echo json_encode($evento); ?>;

    // Configuración de toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "timeOut": "3000"
    };

    console.log('Datos del evento cargados:', window.eventoActual);
    </script>

    <script src="js/convertir_boletin.js"></script>
</body>
</html>