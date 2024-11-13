<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Detalles del Balneario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .form-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .social-media-input {
            position: relative;
        }
        .social-media-input i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
        }
        .social-media-input input {
            padding-left: 35px;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/balneario/mi_balneario/BalnearioController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $balnearioController = new BalnearioController($db);
    $balneario = $balnearioController->obtenerBalneario($_SESSION['id_balneario']);
    ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-building me-2"></i>Editar Detalles del Balneario</h2>
            <a href="ver.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
        </div>

        <form id="formBalneario" action="../../../controllers/balneario/mi_balneario/actualizarDetalles.php" method="POST">
            <!-- Información General -->
            <div class="form-section">
                <h4 class="mb-3">Información General</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del Balneario</label>
                        <input type="text" class="form-control" name="nombre_balneario" 
                               value="<?php echo htmlspecialchars($balneario['nombre_balneario']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion_balneario" 
                               value="<?php echo htmlspecialchars($balneario['direccion_balneario']); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion_balneario" rows="4" required><?php 
                            echo htmlspecialchars($balneario['descripcion_balneario']); 
                        ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Horarios y Precios -->
            <div class="form-section">
                <h4 class="mb-3">Horarios y Precios</h4>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Horario de Apertura</label>
                        <input type="time" class="form-control" name="horario_apertura" 
                               value="<?php echo $balneario['horario_apertura']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Horario de Cierre</label>
                        <input type="time" class="form-control" name="horario_cierre" 
                               value="<?php echo $balneario['horario_cierre']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Precio General</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="precio_general" 
                                   value="<?php echo $balneario['precio_general']; ?>" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="form-section">
                <h4 class="mb-3">Información de Contacto</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" name="telefono_balneario" 
                               value="<?php echo htmlspecialchars($balneario['telefono_balneario']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email_balneario" 
                               value="<?php echo htmlspecialchars($balneario['email_balneario']); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Redes Sociales -->
            <div class="form-section">
                <h4 class="mb-3">Redes Sociales</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Facebook</label>
                        <div class="social-media-input">
                            <i class="bi bi-facebook text-primary"></i>
                            <input type="url" class="form-control" name="facebook_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['facebook_balneario'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instagram</label>
                        <div class="social-media-input">
                            <i class="bi bi-instagram text-danger"></i>
                            <input type="url" class="form-control" name="instagram_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['instagram_balneario'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">X (Twitter)</label>
                        <div class="social-media-input">
                            <i class="bi bi-twitter-x"></i>
                            <input type="url" class="form-control" name="x_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['x_balneario'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TikTok</label>
                        <div class="social-media-input">
                            <i class="bi bi-tiktok"></i>
                            <input type="url" class="form-control" name="tiktok_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['tiktok_balneario'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            // Configurar toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // Manejar el envío del formulario
            $('#formBalneario').on('submit', function(e) {
                e.preventDefault();

                // Validar teléfono
                var telefono = $('input[name="telefono_balneario"]').val();
                var telefonoRegex = /^\d{10}$/;
                
                if (!telefonoRegex.test(telefono)) {
                    toastr.error('Por favor, ingrese un número de teléfono válido (10 dígitos)');
                    return false;
                }

                // Validar horarios
                var apertura = $('input[name="horario_apertura"]').val();
                var cierre = $('input[name="horario_cierre"]').val();
                
                if (apertura && cierre && cierre <= apertura) {
                    toastr.error('El horario de cierre debe ser posterior al horario de apertura');
                    return false;
                }

                // Mostrar indicador de carga
                const btnSubmit = $(this).find('button[type="submit"]');
                const btnText = btnSubmit.html();
                btnSubmit.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

                // Enviar formulario
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json'
                })
                .done(function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Redirigir después de mostrar el mensaje
                        setTimeout(function() {
                            window.location.href = 'ver.php';
                        }, 2000);
                    } else {
                        toastr.error(response.message || 'Error al guardar los cambios');
                    }
                })
                .fail(function(xhr) {
                    const response = xhr.responseJSON || {};
                    toastr.error(response.message || 'Error al procesar la solicitud');
                })
                .always(function() {
                    // Restaurar botón
                    btnSubmit.prop('disabled', false).html(btnText);
                });
            });

            // Validación de horarios en tiempo real
            $('input[name="horario_cierre"]').on('change', function() {
                var apertura = $('input[name="horario_apertura"]').val();
                var cierre = $(this).val();
                
                if (apertura && cierre && cierre <= apertura) {
                    toastr.warning('El horario de cierre debe ser posterior al horario de apertura');
                    $(this).val('');
                }
            });
        });
    </script>
</body>
</html> 