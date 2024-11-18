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
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Editar Detalles del Balneario</h2>
                <p class="text-muted mb-0">
                    <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                </p>
            </div>
            <button type="submit" form="formEditarBalneario" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Guardar Cambios
            </button>
        </div>

        <form id="formEditarBalneario" action="../../../controllers/balneario/mi_balneario/actualizarDetalles.php" method="POST">
            <div class="row">
                <!-- Columna Izquierda -->
                <div class="col-md-6">
                    <!-- Información Básica -->
                    <div class="form-section">
                        <h5 class="mb-4">Información Básica</h5>
                        <div class="mb-3">
                            <label class="form-label">Nombre del Balneario</label>
                            <input type="text" class="form-control" name="nombre_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['nombre_balneario']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion_balneario" rows="4" required><?php 
                                echo htmlspecialchars($balneario['descripcion_balneario']); 
                            ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['direccion_balneario']); ?>" required>
                        </div>
                    </div>

                    <!-- Precios -->
                    <div class="form-section">
                        <h5 class="mb-4">Precios Generales</h5>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text">Adultos $</span>
                                    <input type="number" class="form-control" name="precio_general_adultos" id="precio_general_adultos"
                                           value="<?php echo $balneario['precio_general_adultos']; ?>"
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text">Infantes $</span>
                                    <input type="number" class="form-control" name="precio_general_infantes" id="precio_general_infantes"
                                           value="<?php echo $balneario['precio_general_infantes']; ?>"
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha -->
                <div class="col-md-6">
                    <!-- Horarios -->
                    <div class="form-section">
                        <h5 class="mb-4">Horarios de Atención</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Horario de Apertura</label>
                                <input type="time" class="form-control" name="horario_apertura" 
                                       value="<?php echo $balneario['horario_apertura']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario de Cierre</label>
                                <input type="time" class="form-control" name="horario_cierre" 
                                       value="<?php echo $balneario['horario_cierre']; ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="form-section">
                        <h5 class="mb-4">Información de Contacto</h5>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['telefono_balneario']); ?>" 
                                   pattern="[0-9]{10}" required>
                            <div class="form-text">Formato: 10 dígitos sin espacios</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['email_balneario']); ?>" required>
                        </div>
                    </div>

                    <!-- Redes Sociales -->
                    <div class="form-section">
                        <h5 class="mb-4">Redes Sociales</h5>
                        <div class="mb-3">
                            <label class="form-label">Facebook</label>
                            <div class="social-media-input">
                                <i class="bi bi-facebook text-primary"></i>
                                <input type="url" class="form-control" name="facebook_balneario" 
                                       value="<?php echo htmlspecialchars($balneario['facebook_balneario'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instagram</label>
                            <div class="social-media-input">
                                <i class="bi bi-instagram text-danger"></i>
                                <input type="url" class="form-control" name="instagram_balneario" 
                                       value="<?php echo htmlspecialchars($balneario['instagram_balneario'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">X (Twitter)</label>
                            <div class="social-media-input">
                                <i class="bi bi-twitter-x"></i>
                                <input type="url" class="form-control" name="x_balneario" 
                                       value="<?php echo htmlspecialchars($balneario['x_balneario'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">TikTok</label>
                            <div class="social-media-input">
                                <i class="bi bi-tiktok"></i>
                                <input type="url" class="form-control" name="tiktok_balneario" 
                                       value="<?php echo htmlspecialchars($balneario['tiktok_balneario'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // Almacenar precios iniciales
        let precioAdultos = parseFloat($('#precio_adultos').val());
        let precioInfantes = parseFloat($('#precio_infantes').val());

        // Log inicial de precios
        console.group('Precios Iniciales');
        console.log('Precio Adultos:', precioAdultos);
        console.log('Precio Infantes:', precioInfantes);
        console.groupEnd();

        // Monitorear cambios en precio adultos
        $('#precio_adultos').on('input', function() {
            precioAdultos = parseFloat($(this).val());
            console.group('Actualización Precio Adultos');
            console.log('Nuevo valor:', precioAdultos);
            console.log('Precio Infantes actual:', precioInfantes);
            console.groupEnd();
        });

        // Monitorear cambios en precio infantes
        $('#precio_infantes').on('input', function() {
            precioInfantes = parseFloat($(this).val());
            console.group('Actualización Precio Infantes');
            console.log('Nuevo valor:', precioInfantes);
            console.log('Precio Adultos actual:', precioAdultos);
            console.groupEnd();
        });

        // Manejar el envío del formulario
        $('#formEditarBalneario').on('submit', function(e) {
            e.preventDefault();

            // Obtener los precios directamente de los campos del formulario
            const precioAdultos = parseFloat($('input[name="precio_general_adultos"]').val());
            const precioInfantes = parseFloat($('input[name="precio_general_infantes"]').val());

            // Validaciones
            if (precioAdultos <= 0) {
                toastr.error('El precio para adultos debe ser mayor a 0');
                return false;
            }

            if (precioInfantes <= 0) {
                toastr.error('El precio para infantes debe ser mayor a 0');
                return false;
            }

            // Log de verificación
            console.group('Datos a enviar');
            console.log('Precio Adultos:', precioAdultos);
            console.log('Precio Infantes:', precioInfantes);
            console.log('Formulario completo:', $(this).serialize());
            console.groupEnd();

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