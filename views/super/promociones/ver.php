<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Promoción</title>
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
        .estado-activa { background-color: #198754; color: white; }
        .estado-proxima { background-color: #0dcaf0; color: white; }
        .estado-finalizada { background-color: #6c757d; color: white; }
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
    require_once '../../../controllers/super/promociones/PromocionSuperController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    $id_promocion = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $promocionController = new PromocionSuperController($db);
    $balnearios = $promocionController->obtenerBalnearios();

    // Si es una promoción existente
    $promocion = null;
    $esNueva = true;
    if ($id_promocion > 0) {
        $promocion = $promocionController->obtenerPromocion($id_promocion);
        if (!$promocion) {
            header('Location: lista.php?error=' . urlencode('Promoción no encontrada'));
            exit();
        }
        $esNueva = false;
        $estadisticas = $promocionController->obtenerEstadisticasPromocion($id_promocion);
    }
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><?php echo $esNueva ? 'Nueva Promoción' : 'Editar Promoción'; ?></h2>
                <?php if (!$esNueva): ?>
                <p class="text-muted mb-0">
                    <i class="bi bi-water me-2"></i>
                    <?php echo htmlspecialchars($promocion['nombre_balneario']); ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" form="formPromocion" class="btn btn-primary">
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
                <form id="formPromocion" action="../../../controllers/super/promociones/<?php echo $esNueva ? 'guardar.php' : 'actualizar.php'; ?>" method="POST">
                    <?php if (!$esNueva): ?>
                        <input type="hidden" name="id_promocion" value="<?php echo $id_promocion; ?>">
                    <?php endif; ?>

                    <div class="seccion-formulario">
                        <h5 class="mb-4">Información de la Promoción</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Título de la Promoción</label>
                            <input type="text" class="form-control" name="titulo_promocion" 
                                   value="<?php echo htmlspecialchars($promocion['titulo_promocion'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion_promocion" rows="4" required><?php 
                                echo htmlspecialchars($promocion['descripcion_promocion'] ?? ''); 
                            ?></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control datepicker" name="fecha_inicio_promocion" 
                                       value="<?php echo $promocion['fecha_inicio_promocion'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control datepicker" name="fecha_fin_promocion" 
                                       value="<?php echo $promocion['fecha_fin_promocion'] ?? ''; ?>" required>
                            </div>
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
                                        <?php echo (!$esNueva && $promocion['id_balneario'] == $balneario['id_balneario']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Panel Lateral de Información -->
            <?php if (!$esNueva): ?>
            <div class="col-md-4">
                <div class="seccion-formulario">
                    <h5 class="mb-4">Estado de la Promoción</h5>
                    
                    <div class="text-center mb-4">
                        <span class="estado-badge estado-<?php echo $estadisticas['estado']; ?>">
                            <?php 
                            switch ($estadisticas['estado']) {
                                case 'activa':
                                    echo '<i class="bi bi-check-circle me-2"></i>Activa';
                                    break;
                                case 'proxima':
                                    echo '<i class="bi bi-clock me-2"></i>Próxima';
                                    break;
                                case 'finalizada':
                                    echo '<i class="bi bi-x-circle me-2"></i>Finalizada';
                                    break;
                            }
                            ?>
                        </span>
                    </div>

                    <div class="timeline">
                        <div class="mb-3">
                            <small class="text-muted d-block">Duración</small>
                            <strong><?php echo $estadisticas['duracion_dias']; ?> días</strong>
                        </div>
                    </div>

                    <?php if ($estadisticas['estado'] === 'finalizada'): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta promoción ya ha finalizado y no puede ser editada
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

        // Manejar envío del formulario
        $('#formPromocion').on('submit', function(e) {
            e.preventDefault();

            const btnSubmit = $('button[type="submit"]');
            btnSubmit.prop('disabled', true)
                    .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

            // Validar fechas
            const fechaInicio = new Date($('input[name="fecha_inicio_promocion"]').val());
            const fechaFin = new Date($('input[name="fecha_fin_promocion"]').val());

            if (fechaFin <= fechaInicio) {
                toastr.error('La fecha de fin debe ser posterior a la fecha de inicio');
                btnSubmit.prop('disabled', false)
                       .html('<i class="bi bi-save me-2"></i>Guardar Cambios');
                return false;
            }

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
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