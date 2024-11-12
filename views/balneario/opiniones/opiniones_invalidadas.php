<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opiniones Invalidadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
    $opinionesInvalidadas = $opinionController->obtenerOpinionesInvalidadas($_SESSION['id_balneario']);
    ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-eye-slash me-2"></i>Opiniones Invalidadas</h2>
            <a href="lista.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaInvalidadas" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Valoración</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($opinionesInvalidadas as $opinion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($opinion['nombre_usuario']); ?></td>
                                <td><?php echo str_repeat('⭐', $opinion['valoracion']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($opinion['fecha_registro_opinion'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="verDetalles(<?php echo $opinion['id_opinion']; ?>)">
                                        <i class="bi bi-eye"></i> Ver Detalles
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaInvalidadas').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[2, 'desc']]
            });
        });

        function verDetalles(id) {
            window.location.href = 'ver.php?id=' + id;
        }
    </script>
</body>
</html> 