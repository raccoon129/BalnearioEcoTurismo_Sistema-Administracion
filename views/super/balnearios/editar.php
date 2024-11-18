<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Balneario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        /* Estilos para las secciones del formulario */
        .seccion-formulario {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        /* Estilos para los campos de redes sociales */
        .input-red-social {
            position: relative;
        }

        .input-red-social i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
        }

        .input-red-social input {
            padding-left: 2.5rem;
        }

        /* Estilos para el botón de guardar */
        .btn-flotante {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 100;
            padding: 1rem 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Animación de guardado */
        @keyframes girar {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .icono-guardando {
            animation: girar 1s linear infinite;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    // Importación de dependencias
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/balnearios/BalnearioSuperController.php';

    // Inicialización y verificación de autenticación
    session_start();
    $baseDatos = new Database();
    $conexion = $baseDatos->getConnection();
    $autenticacion = new Auth($conexion);

    $autenticacion->checkAuth();
    $autenticacion->checkRole(['superadministrador']);

    // Obtener datos del balneario
    $idBalneario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $controladorBalneario = new BalnearioSuperController($conexion);
    $balneario = $controladorBalneario->obtenerDetallesBalneario($idBalneario);

    if (!$balneario) {
        header('Location: lista.php?error=' . urlencode('Balneario no encontrado'));
        exit();
    }
    ?>

    <div class="container py-4">
        <!-- Encabezado modificado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Editar Balneario</h2>
                <p class="text-muted mb-0">
                    <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" form="formEditarBalneario" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Cambios
                </button>
                <a href="ver.php?id=<?php echo $idBalneario; ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a Detalles
                </a>
            </div>
        </div>

        <!-- Formulario de edición -->
        <form id="formEditarBalneario" action="../../../controllers/super/balnearios/actualizar.php" method="POST">
            <input type="hidden" name="id_balneario" value="<?php echo $idBalneario; ?>">

            <!-- Información Básica -->
            <div class="seccion-formulario">
                <h5 class="mb-4">Información Básica</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del Balneario</label>
                        <input type="text" class="form-control" name="nombre_balneario" 
                               value="<?php echo htmlspecialchars($balneario['nombre_balneario']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Precios Generales</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text">Adultos $</span>
                                    <input type="number" class="form-control" name="precio_general_adultos" 
                                           value="<?php echo $balneario['precio_general_adultos']; ?>"
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text">Infantes $</span>
                                    <input type="number" class="form-control" name="precio_general_infantes" 
                                           value="<?php echo $balneario['precio_general_infantes']; ?>"
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
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

            <!-- Horarios -->
            <div class="seccion-formulario">
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
            <div class="seccion-formulario">
                <h5 class="mb-4">Información de Contacto</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" name="telefono_balneario" 
                               value="<?php echo htmlspecialchars($balneario['telefono_balneario']); ?>" 
                               pattern="[0-9]{10}" required>
                        <div class="form-text">Formato: 10 dígitos sin espacios</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" name="email_balneario" 
                               value="<?php echo htmlspecialchars($balneario['email_balneario']); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Redes Sociales -->
            <div class="seccion-formulario">
                <h5 class="mb-4">Redes Sociales</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Facebook</label>
                        <div class="input-red-social">
                            <i class="bi bi-facebook text-primary"></i>
                            <input type="url" class="form-control" name="facebook_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['facebook_balneario'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instagram</label>
                        <div class="input-red-social">
                            <i class="bi bi-instagram text-danger"></i>
                            <input type="url" class="form-control" name="instagram_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['instagram_balneario'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">X (Twitter)</label>
                        <div class="input-red-social">
                            <i class="bi bi-twitter-x"></i>
                            <input type="url" class="form-control" name="x_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['x_balneario'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TikTok</label>
                        <div class="input-red-social">
                            <i class="bi bi-tiktok"></i>
                            <input type="url" class="form-control" name="tiktok_balneario" 
                                   value="<?php echo htmlspecialchars($balneario['tiktok_balneario'] ?? ''); ?>">
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
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000"
        };

        // Manejar envío del formulario
        $('#formEditarBalneario').on('submit', function(e) {
            e.preventDefault();
            
            // Validar horarios
            const horaApertura = $('input[name="horario_apertura"]').val();
            const horaCierre = $('input[name="horario_cierre"]').val();
            
            if (horaCierre <= horaApertura) {
                toastr.error('El horario de cierre debe ser posterior al horario de apertura');
                return false;
            }

            // Validar precios
            const precioAdultos = parseFloat($('input[name="precio_general_adultos"]').val());
            const precioInfantes = parseFloat($('input[name="precio_general_infantes"]').val());

            if (precioAdultos <= 0) {
                toastr.error('El precio para adultos debe ser mayor a 0');
                return false;
            }

            if (precioInfantes <= 0) {
                toastr.error('El precio para infantes debe ser mayor a 0');
                return false;
            }

            // Validar teléfono
            const telefono = $('input[name="telefono_balneario"]').val();
            if (!/^\d{10}$/.test(telefono)) {
                toastr.error('El teléfono debe tener exactamente 10 dígitos');
                return false;
            }

            // Deshabilitar botón y mostrar indicador de carga
            const btnGuardar = $('button[type="submit"]');
            const btnTextoOriginal = btnGuardar.html();
            btnGuardar.prop('disabled', true)
                     .html('<i class="bi bi-arrow-repeat icono-guardando me-2"></i>Guardando...');

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
                    setTimeout(() => {
                        window.location.href = 'ver.php?id=' + <?php echo $idBalneario; ?>;
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Error al guardar los cambios');
                }
            })
            .fail(function() {
                toastr.error('Error al procesar la solicitud');
            })
            .always(function() {
                btnGuardar.prop('disabled', false).html(btnTextoOriginal);
            });
        });
    });
    </script>
</body>
</html> 