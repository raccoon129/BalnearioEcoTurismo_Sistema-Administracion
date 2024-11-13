<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
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

        /* Animación para el botón de guardado */
        @keyframes girar {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .icono-guardando {
            animation: girar 1s linear infinite;
        }

        /* Estilos para el avatar */
        .avatar-grande {
            width: 100px;
            height: 100px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: #495057;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/usuarios/UsuarioSuperController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Obtener ID del usuario a editar
    $id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $usuarioController = new UsuarioSuperController($db);
    $usuario = $usuarioController->obtenerUsuario($id_usuario);

    // Verificar solo si el usuario existe
    if (!$usuario) {
        header('Location: lista.php?error=' . urlencode('Usuario no encontrado'));
        exit();
    }

    $balnearios = $usuarioController->obtenerBalnearios();
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Editar Usuario</h2>
                <p class="text-muted mb-0">
                    <?php echo htmlspecialchars($usuario['nombre_usuario']); ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" form="formEditarUsuario" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Guardar Cambios
                </button>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <!-- Formulario de edición -->
        <form id="formEditarUsuario" action="../../../controllers/super/usuarios/actualizar.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">

            <div class="row">
                <!-- Columna izquierda -->
                <div class="col-md-6">
                    <!-- Información básica -->
                    <div class="seccion-formulario">
                        <h5 class="mb-4">Información Personal</h5>
                        
                        <div class="text-center mb-4">
                            <div class="avatar-grande">
                                <?php echo strtoupper(substr($usuario['nombre_usuario'], 0, 1)); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre_usuario" 
                                   value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email_usuario" 
                                   value="<?php echo htmlspecialchars($usuario['email_usuario']); ?>" required>
                        </div>

                        <div class="text-muted small">
                            <i class="bi bi-calendar3 me-2"></i>
                            Registrado el <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="col-md-6">
                    <!-- Rol y asignación -->
                    <div class="seccion-formulario">
                        <h5 class="mb-4">Rol y Asignación</h5>

                        <div class="mb-4">
                            <label class="form-label">Rol del Usuario</label>
                            <select class="form-select" name="rol_usuario" required>
                                <option value="administrador_balneario" 
                                    <?php echo $usuario['rol_usuario'] === 'administrador_balneario' ? 'selected' : ''; ?>>
                                    Administrador de Balneario
                                </option>
                                <option value="superadministrador"
                                    <?php echo $usuario['rol_usuario'] === 'superadministrador' ? 'selected' : ''; ?>>
                                    Superadministrador
                                </option>
                            </select>
                        </div>

                        <div id="seccionBalneario">
                            <label class="form-label">Balneario Asignado</label>
                            <select class="form-select" name="id_balneario">
                                <option value="">Sin asignar</option>
                                <?php foreach ($balnearios as $balneario): ?>
                                <option value="<?php echo $balneario['id_balneario']; ?>"
                                        <?php echo $usuario['id_balneario'] == $balneario['id_balneario'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Seleccione el balneario al que estará asociado este administrador.
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Estado de la cuenta</h6>
                                    <span class="badge bg-success">Activo</span>
                                </div>
                                <?php if ($usuario['total_boletines'] > 0): ?>
                                    <div class="text-muted small">
                                        <i class="bi bi-envelope-fill me-1"></i>
                                        <?php echo $usuario['total_boletines']; ?> boletines enviados
                                    </div>
                                <?php endif; ?>
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
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000"
        };

        // Manejar visibilidad de sección de balneario según el rol
        $('select[name="rol_usuario"]').on('change', function() {
            const seccionBalneario = $('#seccionBalneario');
            const selectBalneario = $('select[name="id_balneario"]');
            
            if (this.value === 'administrador_balneario') {
                seccionBalneario.slideDown();
                selectBalneario.prop('required', true);
            } else {
                seccionBalneario.slideUp();
                selectBalneario.prop('required', false).val('');
            }
        }).trigger('change');

        // Manejar envío del formulario
        $('#formEditarUsuario').on('submit', function(e) {
            e.preventDefault();
            console.log('Formulario enviado');

            const btnSubmit = $('button[type="submit"]');
            btnSubmit.prop('disabled', true)
                    .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

            // Preparar datos del formulario
            const formData = new FormData(this);
            const rol = formData.get('rol_usuario');
            
            // Si es superadministrador, asegurarse de que id_balneario sea null
            if (rol === 'superadministrador') {
                formData.set('id_balneario', '');
            }

            // Validar email
            const email = formData.get('email_usuario');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                toastr.error('Por favor ingrese un email válido');
                btnSubmit.prop('disabled', false)
                        .html('<i class="bi bi-save me-2"></i>Guardar Cambios');
                return false;
            }

            // Validar rol y balneario
            if (rol === 'administrador_balneario' && !formData.get('id_balneario')) {
                toastr.error('Debe seleccionar un balneario para el administrador');
                btnSubmit.prop('disabled', false)
                        .html('<i class="bi bi-save me-2"></i>Guardar Cambios');
                return false;
            }

            // Log para debugging
            //console.log('Datos a enviar:', $(this).serialize());

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json'
            })
            .done(function(response) {
                console.log('Respuesta:', response);
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => window.location.href = 'lista.php', 1500);
                } else {
                    toastr.error(response.message || 'Error al actualizar el usuario');
                    btnSubmit.prop('disabled', false)
                           .html('<i class="bi bi-save me-2"></i>Guardar Cambios');
                }
            })
            .fail(function(xhr) {
                console.error('Error:', xhr.responseJSON);
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