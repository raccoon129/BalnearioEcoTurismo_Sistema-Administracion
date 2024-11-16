<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Boletines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            background: none;
        }
        .nav-tabs .nav-link:hover:not(.active) {
            border-bottom: 3px solid #e9ecef;
        }
        .boletin-card {
            transition: all 0.3s ease;
        }
        .boletin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .estado-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        .estado-borrador { background-color: #ffc107; color: black; }
        .estado-enviado { background-color: #198754; color: white; }
    </style>
</head>
<body class="bg-light">
    <?php
    require_once '../../../config/database.php';
    require_once '../../../config/auth.php';
    require_once '../../../controllers/super/boletines/BoletinSuperController.php';

    session_start();
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());
    ?>

    <div class="container-fluid py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-envelope me-2"></i>Gestión de Boletines</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBoletin">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Boletín
            </button>
        </div>

        <!-- Pestañas -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" id="sistema-tab" data-bs-toggle="tab" href="#sistema">
                    <i class="bi bi-envelope-paper me-2"></i>Boletines del Sistema
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="balnearios-tab" data-bs-toggle="tab" href="#balnearios">
                    <i class="bi bi-water me-2"></i>Boletines de Balnearios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="destinatarios-tab" data-bs-toggle="tab" href="#destinatarios">
                    <i class="bi bi-people me-2"></i>Ver Destinatarios
                </a>
            </li>
        </ul>

        <!-- Contenido de las pestañas -->
        <div class="tab-content">
            <!-- Pestaña de Boletines del Sistema -->
            <div class="tab-pane fade show active" id="sistema">
                <?php include 'components/lista_boletines_sistema.php'; ?>
            </div>

            <!-- Pestaña de Boletines de Balnearios -->
            <div class="tab-pane fade" id="balnearios">
                <?php include 'components/lista_boletines_balneario.php'; ?>
            </div>

            <!-- Pestaña de Destinatarios -->
            <div class="tab-pane fade" id="destinatarios">
                <?php include 'components/lista_destinatarios.php'; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo Boletín -->
    <?php include 'components/modal_boletin.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/boletines.js"></script>
</body>
</html> 