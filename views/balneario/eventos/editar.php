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

// Definir la ruta base
define('BASE_URL', '/administracionBalnearioEcoTurismo/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .form-section {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .preview-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .current-image {
            max-width: 200px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Editar Evento</h2>
                <p class="text-muted mb-0">
                    <?php echo htmlspecialchars($evento['titulo_evento']); ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" form="formEditarEvento" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Cambios
                </button>
                <a href="ver.php?id=<?php echo $id_evento; ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Columna del Formulario -->
            <div class="col-md-8">
                <form id="formEditarEvento" action="../../../controllers/balneario/eventos/actualizar.php" 
                      method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_evento" value="<?php echo $evento['id_evento']; ?>">
                    
                    <!-- Información Básica -->
                    <div class="form-section">
                        <h5 class="mb-4">Información del Evento</h5>
                        <div class="mb-3">
                            <label class="form-label">Título del Evento</label>
                            <input type="text" class="form-control" name="titulo" 
                                   value="<?php echo htmlspecialchars($evento['titulo_evento']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="6" required><?php 
                                echo htmlspecialchars($evento['descripcion_evento']); 
                            ?></textarea>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="form-section">
                        <h5 class="mb-4">Fechas del Evento</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" 
                                       value="<?php echo $evento['fecha_inicio_evento']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control" name="fecha_fin" 
                                       value="<?php echo $evento['fecha_fin_evento']; ?>" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Columna de la Imagen -->
            <div class="col-md-4">
                <div class="form-section">
                    <h5 class="mb-4">Imagen del Evento</h5>
                    
                    <?php if ($evento['url_imagen_evento']): ?>
                        <div class="mb-3">
                            <label class="form-label">Imagen Actual</label>
                            <img src="<?php echo BASE_URL . htmlspecialchars($evento['url_imagen_evento']); ?>" 
                                 alt="Imagen actual" class="current-image d-block">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Cambiar Imagen</label>
                        <input type="file" class="form-control" name="imagen" form="formEditarEvento" 
                               accept="image/*" onchange="previewImage(this)">
                        <div class="form-text">
                            Formatos permitidos: JPG, PNG. Tamaño máximo: 2MB
                        </div>
                    </div>

                    <div id="imagePreview"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
    $(document).ready(function() {
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000"
        };

        // Manejar envío del formulario
        $('#formEditarEvento').on('submit', function(e) {
            e.preventDefault();
            
            // Validar fechas
            const fechaInicio = $('input[name="fecha_inicio"]').val();
            const fechaFin = $('input[name="fecha_fin"]').val();
            
            if (fechaFin < fechaInicio) {
                toastr.error('La fecha de fin no puede ser anterior a la fecha de inicio');
                return false;
            }

            // Preparar datos
            const formData = new FormData(this);
            
            // Deshabilitar botón y mostrar loading
            const btnSubmit = $('button[type="submit"]');
            const btnText = btnSubmit.html();
            btnSubmit.prop('disabled', true)
                    .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

            // Enviar formulario
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        toastr.success('Evento actualizado exitosamente');
                        setTimeout(() => {
                            window.location.href = 'ver.php?id=<?php echo $id_evento; ?>';
                        }, 1500);
                    } else {
                        toastr.error(response.message || 'Error al actualizar el evento');
                    }
                },
                error: function() {
                    toastr.error('Error al procesar la solicitud');
                },
                complete: function() {
                    btnSubmit.prop('disabled', false).html(btnText);
                }
            });
        });
    });

    // Previsualización de imagen
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-image';
                preview.appendChild(img);
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>