<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Boletines</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Toastr CSS -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .tab-content {
            padding: 20px 0;
        }
        
        .table {
            margin-bottom: 0;
            width: 100% !important;
        }
        
        .table th, .table td {
            vertical-align: middle;
            padding: 0.5rem;
        }
        
        .card {
            margin-bottom: 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        /* Ajustes para DataTables */
        .dataTables_wrapper {
            padding: 0;
            width: 100% !important;
        }
        
        .dataTables_filter, .dataTables_length {
            padding: 0.5rem;
        }
        
        .dataTables_info, .dataTables_paginate {
            padding: 0.5rem;
        }
        
        /* Ajustes para los botones de acción */
        .btn-group {
            display: flex;
            gap: 0.25rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
        }
        
        /* Ajustes para las pestañas */
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }
        
        .nav-tabs .nav-link {
            margin-bottom: -2px;
            border: none;
            border-bottom: 2px solid transparent;
        }
        
        .nav-tabs .nav-link.active {
            border-bottom: 2px solid #0d6efd;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-light p-4">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/balneario/boletines/BoletinController.php';

    // Verificar autenticación
    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $boletinController = new BoletinController($db);
    $boletines = $boletinController->obtenerBoletines($_SESSION['id_balneario']);
    ?>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-envelope me-2"></i>Boletines</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBoletin">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Boletín
            </button>
        </div>

        <!-- Alertas de éxito/error -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Después del div container-fluid -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#borradores">
                    <i class="bi bi-pencil-square me-2"></i>Borradores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#enviados">
                    <i class="bi bi-send me-2"></i>Enviados Recientemente
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#historial">
                    <i class="bi bi-clock-history me-2"></i>Historial
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#suscriptores">
                    <i class="bi bi-people me-2"></i>Suscriptores
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Pestaña de Borradores -->
            <div class="tab-pane fade show active" id="borradores">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaBorradores" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Creado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($boletines as $boletin): 
                                        if ($boletin['fecha_envio_boletin'] !== null) continue; ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></td>
                                            <td><?php echo htmlspecialchars($boletin['nombre_usuario']); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="ver.php?id=<?php echo $boletin['id_boletin']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-success" 
                                                            onclick="confirmarEnvio(<?php echo $boletin['id_boletin']; ?>)">
                                                        <i class="bi bi-send"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="confirmarEliminacion(<?php echo $boletin['id_boletin']; ?>)">
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

            <!-- Pestaña de Enviados -->
            <div class="tab-pane fade" id="enviados">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaEnviados" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Fecha de Envío</th>
                                        <th>Enviado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($boletines as $boletin): 
                                        if ($boletin['fecha_envio_boletin'] === null) continue; ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($boletin['fecha_envio_boletin'])); ?></td>
                                            <td><?php echo htmlspecialchars($boletin['nombre_usuario']); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="ver.php?id=<?php echo $boletin['id_boletin']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="confirmarEliminacion(<?php echo $boletin['id_boletin']; ?>)">
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

            <!-- Nueva pestaña de Historial -->
            <div class="tab-pane fade" id="historial">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaHistorial" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Fecha de Envío</th>
                                        <th>Enviado por</th>
                                        <th>Destinatarios</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Obtener historial de boletines enviados con estadísticas
                                    $query = "SELECT b.*, u.nombre_usuario,
                                             (SELECT COUNT(DISTINCT email_usuario) 
                                              FROM opiniones_usuarios 
                                              WHERE id_balneario = u.id_balneario 
                                              AND suscripcion_boletin = 1 
                                              AND fecha_registro_opinion <= b.fecha_envio_boletin) as total_destinatarios
                                             FROM boletines b 
                                             INNER JOIN usuarios u ON b.id_usuario = u.id_usuario 
                                             WHERE u.id_balneario = ? 
                                             AND b.fecha_envio_boletin IS NOT NULL 
                                             ORDER BY b.fecha_envio_boletin DESC";
                                    
                                    $stmt = $db->prepare($query);
                                    $stmt->bind_param("i", $_SESSION['id_balneario']);
                                    $stmt->execute();
                                    $historial = $stmt->get_result();

                                    while ($boletin = $historial->fetch_assoc()):
                                        // Calcular tiempo transcurrido
                                        $fecha_envio = new DateTime($boletin['fecha_envio_boletin']);
                                        $ahora = new DateTime();
                                        $intervalo = $fecha_envio->diff($ahora);
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($boletin['titulo_boletin']); ?></td>
                                        <td>
                                            <span title="<?php echo $fecha_envio->format('d/m/Y H:i'); ?>">
                                                <?php
                                                if ($intervalo->days == 0) {
                                                    echo 'Hoy ' . $fecha_envio->format('H:i');
                                                } elseif ($intervalo->days == 1) {
                                                    echo 'Ayer ' . $fecha_envio->format('H:i');
                                                } else {
                                                    echo $fecha_envio->format('d/m/Y H:i');
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($boletin['nombre_usuario']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $boletin['total_destinatarios']; ?> destinatarios
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="ver.php?id=<?php echo $boletin['id_boletin']; ?>" 
                                                   class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="confirmarEliminacion(<?php echo $boletin['id_boletin']; ?>)"
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nueva pestaña de Suscriptores -->
            <div class="tab-pane fade" id="suscriptores">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaSuscriptores" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Fecha de Suscripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT nombre_usuario, email_usuario, telefono_usuario, fecha_registro_opinion 
                                             FROM opiniones_usuarios 
                                             WHERE id_balneario = ? AND suscripcion_boletin = 1
                                             ORDER BY fecha_registro_opinion DESC";
                                    $stmt = $db->prepare($query);
                                    $stmt->bind_param("i", $_SESSION['id_balneario']);
                                    $stmt->execute();
                                    $suscriptores = $stmt->get_result();
                                    
                                    while ($suscriptor = $suscriptores->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($suscriptor['nombre_usuario']); ?></td>
                                        <td><?php echo htmlspecialchars($suscriptor['email_usuario']); ?></td>
                                        <td><?php echo htmlspecialchars($suscriptor['telefono_usuario'] ?? 'No proporcionado'); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($suscriptor['fecha_registro_opinion'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo Boletín -->
    <div class="modal fade" id="modalBoletin" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Boletín</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formBoletin" action="../../../controllers/balneario/boletines/guardar.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Título del Boletín</label>
                            <input type="text" class="form-control" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contenido</label>
                            <textarea class="form-control" name="contenido" rows="10" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formBoletin" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar como Borrador
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Envío -->
    <div class="modal fade" id="modalConfirmarEnvio" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Envío</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea enviar este boletín a todos los suscriptores?</p>
                    <p class="text-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="../../../controllers/balneario/boletines/enviar.php" method="POST" id="formEnviarBoletin">
                        <input type="hidden" name="id_boletin" id="id_boletin_enviar">
                        <button type="submit" class="btn btn-success" id="btnEnviarBoletin">
                            <i class="bi bi-send me-2"></i>Enviar Boletín
                            <span class="spinner-border spinner-border-sm ms-1 d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar este boletín?</p>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="../../../controllers/balneario/boletines/eliminar.php" method="POST">
                        <input type="hidden" name="id_boletin" id="id_boletin_eliminar">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar Boletín
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            // Configuración común para todas las tablas
            const dataTableConfig = {
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 10,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                columnDefs: [
                    { className: "align-middle", targets: "_all" }
                ]
            };

            // Inicializar tabla de Borradores
            $('#tablaBorradores').DataTable({
                ...dataTableConfig,
                order: [[0, 'asc']]
            });

            // Inicializar tabla de Enviados
            $('#tablaEnviados').DataTable({
                ...dataTableConfig,
                order: [[1, 'desc']] // Ordenar por fecha de envío
            });

            // Inicializar tabla de Suscriptores
            $('#tablaSuscriptores').DataTable({
                ...dataTableConfig,
                order: [[3, 'desc']] // Ordenar por fecha de suscripción
            });

            // Inicializar tabla de Historial
            $('#tablaHistorial').DataTable({
                ...dataTableConfig,
                order: [[1, 'desc']], // Ordenar por fecha de envío
                columnDefs: [
                    {
                        targets: 1, // Columna de fecha
                        type: 'date'
                    }
                ]
            });

            // Ajustar tablas cuando se cambia de pestaña
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });

            // Manejar el envío del boletín
            $('#formEnviarBoletin').on('submit', function(e) {
                const btnEnviar = $('#btnEnviarBoletin');
                const spinner = btnEnviar.find('.spinner-border');
                const textoOriginal = btnEnviar.html();

                // Deshabilitar el botón y mostrar spinner
                btnEnviar.prop('disabled', true);
                spinner.removeClass('d-none');
                btnEnviar.html('<i class="bi bi-send me-2"></i>Enviando...');

                // Restaurar el botón después de 3 segundos si hay error
                setTimeout(function() {
                    if (btnEnviar.prop('disabled')) {
                        btnEnviar.prop('disabled', false);
                        spinner.addClass('d-none');
                        btnEnviar.html(textoOriginal);
                    }
                }, 3000);
            });
        });

        function verBoletin(id) {
            window.location.href = 'ver.php?id=' + id;
        }

        function editarBoletin(id) {
            window.location.href = 'editar.php?id=' + id;
        }

        function confirmarEnvio(id) {
            document.getElementById('id_boletin_enviar').value = id;
            new bootstrap.Modal(document.getElementById('modalConfirmarEnvio')).show();
        }

        function confirmarEliminacion(id) {
            document.getElementById('id_boletin_eliminar').value = id;
            new bootstrap.Modal(document.getElementById('modalConfirmarEliminacion')).show();
        }
    </script>
</body>
</html> 