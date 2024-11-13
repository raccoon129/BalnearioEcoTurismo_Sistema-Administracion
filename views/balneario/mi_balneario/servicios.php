<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Servicios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
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
    $servicios = $balnearioController->obtenerServicios($_SESSION['id_balneario']);
    ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-gear me-2"></i>Gestión de Servicios</h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalServicio">
                    <i class="bi bi-plus-lg me-2"></i>Nuevo Servicio
                </button>
                <a href="ver.php" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaServicios" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Descripción</th>
                                <th>Precio Adicional</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $servicio): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($servicio['nombre_servicio']); ?></td>
                                <td><?php echo htmlspecialchars($servicio['descripcion_servicio']); ?></td>
                                <td>$<?php echo number_format($servicio['precio_adicional'], 2); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-danger" onclick="eliminarServicio(<?php echo $servicio['id_servicio']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

    <!-- Modal para Nuevo/Editar Servicio -->
    <div class="modal fade" id="modalServicio" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formServicio">
                        <input type="hidden" name="id_servicio" id="id_servicio">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Servicio</label>
                            <input type="text" class="form-control" name="nombre_servicio" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion_servicio" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio Adicional</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="precio_adicional" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formServicio" class="btn btn-primary">Guardar</button>
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

    <!-- Incluir el script de gestión de servicios que ya teníamos -->
    <script>
        // ... (aquí va el script que ya teníamos para la gestión de servicios)
    </script>

    <!-- Añadir este script al final del archivo servicios.php -->
    <script>
    $(document).ready(function() {
        // Configurar toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // Inicializar DataTable
        $('#tablaServicios').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[0, 'asc']]
        });

        // Manejar envío del formulario de servicio
        $('#formServicio').on('submit', function(e) {
            e.preventDefault();

            // Validar precio
            const precio = parseFloat($('input[name="precio_adicional"]').val());
            if (isNaN(precio) || precio < 0) {
                toastr.error('Por favor ingrese un precio válido');
                return false;
            }

            // Deshabilitar botón y mostrar carga
            const btnSubmit = $(this).find('button[type="submit"]');
            const btnText = btnSubmit.html();
            btnSubmit.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

            $.ajax({
                url: '../../../controllers/balneario/mi_balneario/guardarServicio.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#modalServicio').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.message || 'Error al guardar el servicio');
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON || {};
                toastr.error(response.message || 'Error al procesar la solicitud');
            })
            .always(function() {
                btnSubmit.prop('disabled', false).html(btnText);
            });
        });

        // Formatear input de precio en tiempo real
        $('input[name="precio_adicional"]').on('input', function() {
            let value = $(this).val().replace(/[^\d.]/g, '');
            const parts = value.split('.');
            if (parts.length > 1) {
                parts[1] = parts[1].slice(0, 2);
                value = parts.join('.');
            }
            $(this).val(value);
        });

        // Limpiar formulario al abrir modal
        $('#modalServicio').on('show.bs.modal', function() {
            $('#formServicio')[0].reset();
            $('#id_servicio').val('');
            $('.modal-title').text('Nuevo Servicio');
        });
    });

    // Función para editar servicio
    function editarServicio(id) {
        $.get('../../../controllers/balneario/mi_balneario/obtenerServicio.php', { id_servicio: id })
            .done(function(response) {
                if (response.success) {
                    const servicio = response.data;
                    $('#id_servicio').val(servicio.id_servicio);
                    $('input[name="nombre_servicio"]').val(servicio.nombre_servicio);
                    $('textarea[name="descripcion_servicio"]').val(servicio.descripcion_servicio);
                    $('input[name="precio_adicional"]').val(servicio.precio_adicional);
                    $('.modal-title').text('Editar Servicio');
                    $('#modalServicio').modal('show');
                } else {
                    toastr.error(response.message);
                }
            })
            .fail(function() {
                toastr.error('Error al cargar los datos del servicio');
            });
    }

    // Función para eliminar servicio
    function eliminarServicio(id) {
        if (confirm('¿Está seguro que desea eliminar este servicio?')) {
            $.post('../../../controllers/balneario/mi_balneario/eliminarServicio.php', { id_servicio: id })
                .done(function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail(function() {
                    toastr.error('Error al eliminar el servicio');
                });
        }
    }
    </script>
</body>
</html> 