<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <!-- DatePicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .seccion-formulario {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .estado-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .estado-activo { background-color: #198754; color: white; }
        .estado-proximo { background-color: #0dcaf0; color: white; }
        .estado-finalizado { background-color: #6c757d; color: white; }
        .preview-imagen {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-top: 10px;
        }
        .imagen-placeholder {
            width: 100%;
            height: 200px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            cursor: pointer;
        }
        .imagen-placeholder:hover {
            background-color: #e9ecef;
        }
        .timeline {
            position: relative;
            padding: 1rem 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #dee2e6;
            transform: translateX(-50%);
        }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/eventos/EventoSuperController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    $id_evento = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $eventoController = new EventoSuperController($db);
    $balnearios = $eventoController->obtenerBalnearios();

    // Si es un evento existente
    $evento = null;
    $esNuevo = true;
    if ($id_evento > 0) {
        $evento = $eventoController->obtenerEvento($id_evento);
        if (!$evento) {
            header('Location: lista.php?error=' . urlencode('Evento no encontrado'));
            exit();
        }
        $esNuevo = false;
    }
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><?php echo $esNuevo ? 'Nuevo Evento' : 'Editar Evento'; ?></h2>
                <?php if (!$esNuevo): ?>
                <p class="text-muted mb-0">
                    <i class="bi bi-water me-2"></i>
                    <?php echo htmlspecialchars($evento['nombre_balneario']); ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" form="formEvento" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Cambios
                </button>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Formulario Principal -->
            <div class="col-md-8">
                <form id="formEvento" action="../../../controllers/super/eventos/<?php echo $esNuevo ? 'guardar.php' : 'actualizar.php'; ?>" 
                      method="POST" enctype="multipart/form-data">
                    <?php if (!$esNuevo): ?>
                        <input type="hidden" name="id_evento" value="<?php echo $id_evento; ?>">
                    <?php endif; ?>

                    <div class="seccion-formulario">
                        <h5 class="mb-4">Información del Evento</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Título del Evento</label>
                            <input type="text" class="form-control" name="titulo_evento" 
                                   value="<?php echo htmlspecialchars($evento['titulo_evento'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion_evento" rows="4" required><?php 
                                echo htmlspecialchars($evento['descripcion_evento'] ?? ''); 
                            ?></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control datepicker" name="fecha_inicio_evento" 
                                       value="<?php echo $evento['fecha_inicio_evento'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control datepicker" name="fecha_fin_evento" 
                                       value="<?php echo $evento['fecha_fin_evento'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="seccion-formulario">
                        <h5 class="mb-4">Imagen del Evento (Opcional)</h5>
                        
                        <div class="mb-3">
                            <input type="file" class="form-control" name="imagen_evento" id="imagen_evento" 
                                   accept="image/*">
                            <div class="form-text">
                                Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 5MB. 
                                La imagen es opcional y puede añadirse después.
                            </div>
                        </div>

                        <div id="previewContainer">
                            <?php if (!$esNuevo && $evento['url_imagen_evento']): ?>
                                <img src="../../../<?php echo htmlspecialchars($evento['url_imagen_evento']); ?>" 
                                     class="preview-imagen" alt="Imagen actual del evento">
                            <?php else: ?>
                                <div class="imagen-placeholder" onclick="document.getElementById('imagen_evento').click()">
                                    <i class="bi bi-image display-4 text-muted"></i>
                                    <div class="text-muted mt-2">Click para seleccionar una imagen (opcional)</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="seccion-formulario">
                        <h5 class="mb-4">Asignación de Balneario</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Balneario</label>
                            <select class="form-select" name="id_balneario" required>
                                <option value="">Seleccione un balneario</option>
                                <?php foreach ($balnearios as $balneario): ?>
                                <option value="<?php echo $balneario['id_balneario']; ?>"
                                        <?php echo (!$esNuevo && $evento['id_balneario'] == $balneario['id_balneario']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Panel Lateral de Información -->
            <?php if (!$esNuevo): ?>
            <div class="col-md-4">
                <div class="seccion-formulario">
                    <h5 class="mb-4">Estado del Evento</h5>
                    
                    <div class="text-center mb-4">
                        <span class="estado-badge estado-<?php echo $evento['estado_evento']; ?>">
                            <?php 
                            switch ($evento['estado_evento']) {
                                case 'activo':
                                    echo '<i class="bi bi-check-circle me-2"></i>Activo';
                                    break;
                                case 'proximo':
                                    echo '<i class="bi bi-clock me-2"></i>Próximo';
                                    break;
                                case 'finalizado':
                                    echo '<i class="bi bi-x-circle me-2"></i>Finalizado';
                                    break;
                            }
                            ?>
                        </span>
                    </div>

                    <div class="timeline">
                        <div class="mb-3">
                            <small class="text-muted d-block">Duración</small>
                            <strong><?php echo $evento['duracion_dias']; ?> días</strong>
                        </div>
                    </div>

                    <?php if ($evento['estado_evento'] === 'finalizado'): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Este evento ya ha finalizado y no puede ser editado
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
    $(document).ready(function() {
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000"
        };

        // Inicializar DatePicker
        flatpickr(".datepicker", {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: "today"
        });

        // Preview de imagen
        $('#imagen_evento').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    toastr.error('La imagen no debe superar los 5MB');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewContainer').html(`
                        <img src="${e.target.result}" class="preview-imagen" alt="Vista previa">
                    `);
                };
                reader.readAsDataURL(file);
            }
        });

        // Manejar envío del formulario
        $('#formEvento').on('submit', function(e) {
            e.preventDefault();

            const btnSubmit = $('button[type="submit"]');
            btnSubmit.prop('disabled', true)
                    .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

            // Validar fechas
            const fechaInicio = new Date($('input[name="fecha_inicio_evento"]').val());
            const fechaFin = new Date($('input[name="fecha_fin_evento"]').val());

            if (fechaFin <= fechaInicio) {
                toastr.error('La fecha de fin debe ser posterior a la fecha de inicio');
                btnSubmit.prop('disabled', false)
                       .html('<i class="bi bi-save me-2"></i>Guardar Cambios');
                return false;
            }

            // Enviar formulario
            const formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => window.location.href = 'lista.php', 1500);
                } else {
                    toastr.error(response.message);
                    btnSubmit.prop('disabled', false)
                           .html('<i class="bi bi-save me-2"></i>Guardar Cambios');
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON || {};
                toastr.error(response.message || 'Error al procesar la solicitud');
                btnSubmit.prop('disabled', false)
                       .html('<i class="bi bi-save me-2"></i>Guardar Cambios');
            });
        });
    });
    </script>
</body>
</html> 