<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Boletín</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
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

    $id_boletin = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());
    $boletin = $boletinController->obtenerDetallesBoletinSuperAdmin($id_boletin);

    if (!$boletin || $boletin['estado_boletin'] !== 'borrador') {
        header('Location: lista.php');
        exit();
    }
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Editar Boletín</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Los cambios se guardarán automáticamente como borrador
                </p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" onclick="mostrarModalEnvio(<?php echo $id_boletin; ?>)">
                    <i class="bi bi-send me-2"></i>Enviar Boletín
                </button>
                <a href="lista.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <!-- Formulario de edición -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="formEditarBoletin" action="../../../controllers/super/boletines/actualizar.php" method="POST">
                            <input type="hidden" name="id_boletin" value="<?php echo $id_boletin; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Título del Boletín <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="titulo_boletin" 
                                       value="<?php echo htmlspecialchars($boletin['titulo_boletin']); ?>" required>
                                <div class="form-text">
                                    El título debe ser claro y conciso.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contenido <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="contenido_boletin" rows="10" required><?php 
                                    echo htmlspecialchars($boletin['contenido_boletin']); 
                                ?></textarea>
                                <div class="form-text">
                                    Escriba el contenido completo del boletín. Puede usar saltos de línea para mejor organización.
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de envío -->
    <?php include 'components/modal_envio_boletin_superadministrador.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/boletines.js"></script>

    <script>
    $(document).ready(function() {
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000"
        };

        // Manejar envío del formulario
        $('#formEditarBoletin').on('submit', function(e) {
            e.preventDefault();

            const btnSubmit = $(this).find('button[type="submit"]');
            const btnHtml = btnSubmit.html();
            btnSubmit.prop('disabled', true)
                    .html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');

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
                        window.location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message);
                    btnSubmit.prop('disabled', false).html(btnHtml);
                }
            })
            .fail(function() {
                toastr.error('Error al procesar la solicitud');
                btnSubmit.prop('disabled', false).html(btnHtml);
            });
        });
    });
    </script>
</body>
</html> 