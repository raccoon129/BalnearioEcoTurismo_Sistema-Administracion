<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Balnearios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .card-stats {
            transition: transform 0.2s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/balnearios/BalnearioSuperController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    $balnearioController = new BalnearioSuperController($db);
    $balnearios = $balnearioController->obtenerBalnearios();
    ?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-water me-2"></i>Gestión de Balnearios</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBalneario">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Balneario
            </button>
        </div>

        <div class="row">
            <?php foreach ($balnearios as $balneario): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card card-stats h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="card-title"><?php echo htmlspecialchars($balneario['nombre_balneario']); ?></h5>
                            <div class="dropdown">
                                <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="ver.php?id=<?php echo $balneario['id_balneario']; ?>">
                                            <i class="bi bi-eye me-2"></i>Ver Detalles
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="editar.php?id=<?php echo $balneario['id_balneario']; ?>">
                                            <i class="bi bi-pencil me-2"></i>Editar
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <p class="text-muted">
                            <i class="bi bi-geo-alt me-2"></i>
                            <?php echo htmlspecialchars($balneario['direccion_balneario']); ?>
                        </p>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">Usuarios</small>
                                    <strong><?php echo $balneario['total_usuarios']; ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">Eventos</small>
                                    <strong><?php echo $balneario['total_eventos']; ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">Promociones</small>
                                    <strong><?php echo $balneario['total_promociones']; ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">Opiniones</small>
                                    <strong><?php echo $balneario['total_opiniones']; ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-info status-badge">
                                    <i class="bi bi-clock me-1"></i>
                                    <?php 
                                    echo date('h:i A', strtotime($balneario['horario_apertura'])) . 
                                         ' - ' . 
                                         date('h:i A', strtotime($balneario['horario_cierre'])); 
                                    ?>
                                </span>
                            </div>
                            <div>
                                <span class="badge bg-success status-badge">
                                    <i class="bi bi-currency-dollar me-1"></i>
                                    <?php echo number_format($balneario['precio_general'], 2); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal para Nuevo Balneario -->
    <div class="modal fade" id="modalBalneario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Balneario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formBalneario" action="../../../controllers/super/balnearios/guardar.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Balneario</label>
                                <input type="text" class="form-control" name="nombre_balneario" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Precio General</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="precio_general" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" name="direccion_balneario" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion_balneario" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario de Apertura</label>
                                <input type="time" class="form-control" name="horario_apertura" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario de Cierre</label>
                                <input type="time" class="form-control" name="horario_cierre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono_balneario" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email_balneario" required>
                            </div>
                            <div class="col-12">
                                <h6 class="mb-3">Redes Sociales (Opcional)</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Facebook</label>
                                <input type="url" class="form-control" name="facebook_balneario">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Instagram</label>
                                <input type="url" class="form-control" name="instagram_balneario">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">X (Twitter)</label>
                                <input type="url" class="form-control" name="x_balneario">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">TikTok</label>
                                <input type="url" class="form-control" name="tiktok_balneario">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formBalneario" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar Balneario
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
        $(document).ready(function() {
            // Configurar toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "timeOut": "3000"
            };

            // Referencia al modal
            const modalBalneario = new bootstrap.Modal(document.getElementById('modalBalneario'));

            // Validación del formulario
            $('#formBalneario').on('submit', function(e) {
                e.preventDefault();
                
                // Validar horarios
                const horaApertura = $('input[name="horario_apertura"]').val();
                const horaCierre = $('input[name="horario_cierre"]').val();
                
                if (horaCierre <= horaApertura) {
                    toastr.error('El horario de cierre debe ser posterior al horario de apertura');
                    return false;
                }

                // Validar teléfono
                const telefono = $('input[name="telefono_balneario"]').val();
                if (!/^\d{10}$/.test(telefono)) {
                    toastr.error('El teléfono debe tener exactamente 10 dígitos');
                    return false;
                }

                // Deshabilitar botón y mostrar indicador de carga
                const btnSubmit = $(this).find('button[type="submit"]');
                const btnText = btnSubmit.html();
                
                btnSubmit.prop('disabled', true)
                    .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json'
                })
                .done(function(response) {
                    if (response.success) {
                        // Cerrar modal con animación
                        modalBalneario.hide();
                        
                        // Mostrar mensaje de éxito
                        toastr.success(response.message);
                        
                        // Recargar página después de un breve delay
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(response.message);
                        // Reactivar botón en caso de error
                        btnSubmit.prop('disabled', false).html(btnText);
                    }
                })
                .fail(function(xhr) {
                    const response = xhr.responseJSON || {};
                    toastr.error(response.message || 'Error al procesar la solicitud');
                    // Reactivar botón en caso de error
                    btnSubmit.prop('disabled', false).html(btnText);
                });
            });

            // Limpiar formulario cuando se cierre el modal
            $('#modalBalneario').on('hidden.bs.modal', function() {
                $('#formBalneario')[0].reset();
                // Reactivar botón si estaba deshabilitado
                const btnSubmit = $(this).find('button[type="submit"]');
                btnSubmit.prop('disabled', false)
                    .html('<i class="bi bi-save me-2"></i>Guardar Balneario');
            });

            // Validación de horarios en tiempo real
            $('input[name="horario_cierre"]').on('change', function() {
                const apertura = $('input[name="horario_apertura"]').val();
                const cierre = $(this).val();
                
                if (apertura && cierre && cierre <= apertura) {
                    toastr.warning('El horario de cierre debe ser posterior al horario de apertura');
                    $(this).val('');
                }
            });
        });
    </script>
</body>
</html> 