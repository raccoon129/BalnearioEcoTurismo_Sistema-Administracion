<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .avatar-inicial {
            width: 35px;
            height: 35px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #495057;
        }

        .password-container {
            position: relative;
        }

        .password-container .btn-copy {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.02);
            cursor: pointer;
        }

        .badge-outline {
            background-color: transparent;
            border: 1px solid;
        }

        .badge-outline.badge-success {
            color: #198754;
            border-color: #198754;
        }

        .badge-outline.badge-warning {
            color: #ffc107;
            border-color: #ffc107;
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

    $usuarioController = new UsuarioSuperController($db);
    $usuarios = $usuarioController->obtenerUsuarios();
    $balnearios = $usuarioController->obtenerBalnearios(); // Para el select de balnearios
    ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-people me-2"></i>Gestión de Usuarios</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaUsuarios" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Balneario Asignado</th>
                                <th>Fecha de Registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-inicial me-2">
                                            <?php echo strtoupper(substr($usuario['nombre_usuario'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="bi bi-envelope me-2 text-muted"></i>
                                    <?php echo htmlspecialchars($usuario['email_usuario']); ?>
                                </td>
                                <td>
                                    <?php if ($usuario['rol_usuario'] === 'administrador_balneario'): ?>
                                        <span class="badge bg-primary">Administrador</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Superadministrador</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($usuario['nombre_balneario']): ?>
                                        <span class="badge badge-outline badge-success">
                                            <?php echo htmlspecialchars($usuario['nombre_balneario']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-outline badge-warning">Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar2 me-2 text-muted"></i>
                                    <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                                </td>
                                <td>
                                    <span class="badge bg-success">Activo</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="editar.php?id=<?php echo $usuario['id_usuario']; ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Editar usuario">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <button type="button" 
                                                class="btn btn-sm btn-warning text-white" 
                                                onclick="regenerarPassword(<?php echo $usuario['id_usuario']; ?>)"
                                                title="Regenerar contraseña">
                                            <i class="bi bi-key"></i>
                                        </button>

                                        <?php if ($usuario['total_boletines'] == 0): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="eliminarUsuario(<?php echo $usuario['id_usuario']; ?>)"
                                                    title="Eliminar usuario">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    disabled 
                                                    title="No se puede eliminar - Usuario con boletines">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo/Editar Usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formUsuario" action="../../../controllers/super/usuarios/guardar.php" method="POST">
                        <input type="hidden" name="id_usuario" id="id_usuario">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre_usuario" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email_usuario" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" name="rol_usuario" required>
                                <option value="administrador_balneario">Administrador de Balneario</option>
                                <option value="superadministrador">Superadministrador</option>
                            </select>
                        </div>
                        
                        <div id="seccionAsignacionBalneario" class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="asignarDespues">
                                <label class="form-check-label" for="asignarDespues">
                                    Asignar balneario después
                                </label>
                            </div>
                            
                            <div id="seccionBalneario">
                                <label class="form-label">Balneario Asignado</label>
                                <select class="form-select" name="id_balneario">
                                    <option value="">Seleccione un balneario</option>
                                    <?php foreach ($balnearios as $balneario): ?>
                                    <option value="<?php echo $balneario['id_balneario']; ?>">
                                        <?php echo htmlspecialchars($balneario['nombre_balneario']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Seleccione el balneario al que estará asociado este administrador.
                                </div>
                            </div>
                        </div>

                        <!-- Contenedor para mostrar la contraseña generada -->
                        <div id="passwordContainer" class="mb-3 d-none">
                            <label class="form-label">Contraseña Generada</label>
                            <div class="password-container">
                                <input type="text" class="form-control" id="passwordGenerada" readonly>
                                <button type="button" class="btn btn-outline-secondary btn-copy" onclick="copiarPassword()">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Guarde esta contraseña, no podrá verla nuevamente.
                            </small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formUsuario" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
    $(document).ready(function() {
        // Configuración de DataTable
        $('#tablaUsuarios').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[4, 'desc']], // Ordenar por fecha de registro
            responsive: true
        });

        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000"
        };

        // Referencia al modal
        const modalUsuario = new bootstrap.Modal(document.getElementById('modalUsuario'));

        // Manejar checkbox de asignación posterior
        $('#asignarDespues').on('change', function() {
            const seccionBalneario = $('#seccionBalneario');
            const selectBalneario = $('select[name="id_balneario"]');
            
            if (this.checked) {
                seccionBalneario.slideUp();
                selectBalneario.prop('required', false).prop('disabled', true);
            } else {
                seccionBalneario.slideDown();
                if ($('select[name="rol_usuario"]').val() === 'administrador_balneario') {
                    selectBalneario.prop('required', true).prop('disabled', false);
                }
            }
        });

        // Actualizar manejo del rol
        $('select[name="rol_usuario"]').on('change', function() {
            const seccionAsignacion = $('#seccionAsignacionBalneario');
            const seccionBalneario = $('#seccionBalneario');
            const selectBalneario = $('select[name="id_balneario"]');
            const checkAsignarDespues = $('#asignarDespues');
            
            if (this.value === 'administrador_balneario') {
                seccionAsignacion.slideDown();
                if (!checkAsignarDespues.prop('checked')) {
                    seccionBalneario.slideDown();
                    selectBalneario.prop('required', true).prop('disabled', false);
                }
            } else {
                seccionAsignacion.slideUp();
                seccionBalneario.slideUp();
                selectBalneario.prop('required', false).prop('disabled', true);
                checkAsignarDespues.prop('checked', false);
            }
        });

        // Manejar envío del formulario
        $('#formUsuario').on('submit', function(e) {
            e.preventDefault();

            // Obtener referencia al botón de submit y al botón de cancelar
            const btnSubmit = $(this).find('button[type="submit"]');
            const btnCancel = $('.modal-footer .btn-secondary');
            
            // Evitar doble clic deshabilitando inmediatamente ambos botones
            btnSubmit.prop('disabled', true);
            btnCancel.prop('disabled', true);
            
            // Validar campos
            const email = $('input[name="email_usuario"]').val();
            if (!isValidEmail(email)) {
                toastr.error('Por favor ingrese un email válido');
                btnSubmit.prop('disabled', false);
                btnCancel.prop('disabled', false);
                return false;
            }

            // Validar selección de balneario para administradores
            if ($('select[name="rol_usuario"]').val() === 'administrador_balneario' && 
                !$('#asignarDespues').prop('checked') && 
                !$('select[name="id_balneario"]').val()) {
                toastr.error('Debe seleccionar un balneario o marcar "Asignar balneario después"');
                btnSubmit.prop('disabled', false);
                btnCancel.prop('disabled', false);
                return false;
            }

            // Cambiar apariencia del botón mientras se procesa
            const btnText = btnSubmit.html();
            btnSubmit.html('<i class="bi bi-hourglass-split me-2 icono-guardando"></i>Guardando...');

            // Realizar petición AJAX
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    // Mostrar contraseña generada
                    $('#passwordGenerada').val(response.password);
                    $('#passwordContainer').removeClass('d-none');
                    
                    // Deshabilitar todos los campos del formulario y botones
                    $('#formUsuario input, #formUsuario select').prop('disabled', true);
                    btnSubmit.remove(); // Eliminar el botón de guardar
                    btnCancel.prop('disabled', true).hide(); // Ocultar el botón de cancelar
                    
                    // Añadir nuevo botón de copiar y cerrar
                    $('.modal-footer').html(`
                        <button type="button" class="btn btn-success" onclick="copiarYCerrar()">
                            <i class="bi bi-clipboard-check me-2"></i>Copiar y Cerrar
                        </button>
                    `);
                    
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                    // Restaurar botones en caso de error
                    btnSubmit.prop('disabled', false).html(btnText);
                    btnCancel.prop('disabled', false);
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON || {};
                toastr.error(response.message || 'Error al procesar la solicitud');
                // Restaurar botones en caso de error
                btnSubmit.prop('disabled', false).html(btnText);
                btnCancel.prop('disabled', false);
            });
        });

        // Actualizar función regenerarPassword
        window.regenerarPassword = function(idUsuario) {
            if (!confirm('¿Está seguro de regenerar la contraseña?')) return;

            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

            $.post('../../../controllers/super/usuarios/regenerarPassword.php', {
                id_usuario: idUsuario
            })
            .done(function(response) {
                if (response.success) {
                    $('#passwordGenerada').val(response.password);
                    $('#passwordContainer').removeClass('d-none');
                    modalUsuario.show();
                    toastr.success('Contraseña regenerada exitosamente');
                } else {
                    toastr.error(response.message);
                }
            })
            .fail(function() {
                toastr.error('Error al regenerar la contraseña');
            })
            .always(function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        };

        // Actualizar función eliminarUsuario
        window.eliminarUsuario = function(idUsuario) {
            if (!confirm('¿Está seguro de eliminar este usuario?')) return;

            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

            $.post('../../../controllers/super/usuarios/eliminar.php', {
                id_usuario: idUsuario
            })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .fail(function() {
                toastr.error('Error al eliminar el usuario');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        };

        // Función para copiar contraseña
        window.copiarPassword = function() {
            const password = $('#passwordGenerada').val();
            navigator.clipboard.writeText(password).then(function() {
                toastr.success('Contraseña copiada al portapapeles');
            });
        };

        // Función para copiar y cerrar
        window.copiarYCerrar = function() {
            copiarPassword();
            setTimeout(() => {
                modalUsuario.hide();
                location.reload();
            }, 1000);
        };

        // Función auxiliar para validar email
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        // Limpiar formulario al cerrar modal
        $('#modalUsuario').on('hidden.bs.modal', function() {
            $('#formUsuario')[0].reset();
            $('#formUsuario input, #formUsuario select').prop('disabled', false);
            $('#passwordContainer').addClass('d-none');
            $('#seccionBalneario').show();
            $('#asignarDespues').prop('checked', false);
            const btnSubmit = $('#formUsuario button[type="submit"]');
            btnSubmit.prop('disabled', false)
                   .html('<i class="bi bi-save me-2"></i>Guardar Usuario');
        });
    });
    </script>
</body>
</html> 