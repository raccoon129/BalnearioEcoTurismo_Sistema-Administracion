<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Opiniones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
</head>

<body class="bg-light p-4">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/balneario/opiniones/OpinionController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $opinionController = new OpinionController($db);
    $opinionesValidadas = $opinionController->obtenerOpinionesValidadas($_SESSION['id_balneario']);
    $opinionesPendientes = $opinionController->obtenerOpinionesPendientes($_SESSION['id_balneario']);
    ?>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-chat-dots me-2"></i>Gestión de Opiniones</h2>
            <a href="opiniones_invalidadas.php" class="btn btn-secondary">
                <i class="bi bi-eye-slash me-2"></i>Ver Opiniones Invalidadas
            </a>
        </div>

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

        <?php if (isset($_GET['success']) || isset($_GET['error'])): ?>
            <script>
                // Esperar 1.5 segundos antes de recargar para que el usuario pueda ver el mensaje
                setTimeout(function() {
                    window.location.href = 'lista.php';
                }, 1500);
            </script>
        <?php endif; ?>

        <div class="row">
            <!-- Columna de Opiniones Validadas -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Opiniones Validadas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaValidadas" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Valoración</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($opinionesValidadas as $opinion): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></td>
                                            <td><?php echo str_repeat('⭐', $opinion['valoracion']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($opinion['fecha_registro_opinion'])); ?></td>
                                            <td>
                                                <a href="ver.php?id=<?php echo $opinion['id_opinion']; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> Ver Detalles
                                                </a>
                                                <form action="../../../controllers/balneario/opiniones/validarOpinion.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="id_opinion" value="<?php echo $opinion['id_opinion']; ?>">
                                                    <input type="hidden" name="validada" value="0">
                                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Desea invalidar esta opinión?')">
                                                        <i class="bi bi-x-circle"></i> Invalidar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna de Opiniones Pendientes -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Pendientes de Validación</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaPendientes" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($opinionesPendientes as $opinion): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($opinion['fecha_registro_opinion'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <form action="../../../controllers/balneario/opiniones/validarOpinion.php" method="POST" style="display:inline;">
                                                        <input type="hidden" name="id_opinion" value="<?php echo $opinion['id_opinion']; ?>">
                                                        <input type="hidden" name="validada" value="1">
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('¿Desea validar esta opinión?')">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="../../../controllers/balneario/opiniones/validarOpinion.php" method="POST" style="display:inline;">
                                                        <input type="hidden" name="id_opinion" value="<?php echo $opinion['id_opinion']; ?>">
                                                        <input type="hidden" name="validada" value="0">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Desea invalidar esta opinión?')">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <a href="ver.php?id=<?php echo $opinion['id_opinion']; ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
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
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#tablaValidadas, #tablaPendientes').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[2, 'desc']]
            });

            // Configurar toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "timeOut": "1500"
            };

            // Manejar envío de formularios con AJAX
            $('form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            // Recargar la página después de 1.5 segundos
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'Error al procesar la solicitud');
                        }
                    },
                    error: function() {
                        toastr.error('Error al procesar la solicitud');
                    }
                });
            });

            // Mostrar mensajes si existen en la URL
            <?php if (isset($_GET['success'])): ?>
                toastr.success('<?php echo htmlspecialchars($_GET['success']); ?>');
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                toastr.error('<?php echo htmlspecialchars($_GET['error']); ?>');
            <?php endif; ?>
        });
    </script>
</body>

</html>