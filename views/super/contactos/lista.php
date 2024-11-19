<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Contactos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/contactos/ContactoController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    $contactoController = new ContactoController($db);
    $contactos = $contactoController->obtenerContactos();
    ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-person-lines-fill me-2"></i>Gestión de Contactos</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaContactos" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Suscrito</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contactos as $contacto): ?>
                                <tr>
                                    <td><?php echo $contacto['id_contacto']; ?></td>
                                    <td><?php echo htmlspecialchars($contacto['nombre_usuario_contacto']); ?></td>
                                    <td><?php echo htmlspecialchars($contacto['telefono_usuario_contacto']); ?></td>
                                    <td><?php echo htmlspecialchars($contacto['email_usuario_contacto'] ?? 'No proporcionado'); ?></td>
                                    <td>
                                        <?php if ($contacto['suscripcion_boletin']): ?>
                                            <span class="badge bg-success">Sí</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="ver.php?id=<?php echo $contacto['id_contacto']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger"
                                                onclick="confirmarEliminacion(<?php echo $contacto['id_contacto']; ?>)">
                                            <i class="bi bi-trash"></i>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/contactos.js"></script>
</body>
</html>
