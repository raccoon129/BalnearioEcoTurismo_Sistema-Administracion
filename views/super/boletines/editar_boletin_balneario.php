<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Boletín del Balneario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <style>
        .preview-content {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-top: 1rem;
        }
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

    $id_boletin = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());
    $boletin = $boletinController->obtenerDetallesBoletinBalneario($id_boletin);

    if (!$boletin || $boletin['estado_boletin'] !== 'borrador') {
        header('Location: balneario.php?id=' . $boletin['id_balneario']);
        exit();
    }
    ?>

    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Editar Boletín</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-water me-2"></i>
                    <?php echo htmlspecialchars($boletin['nombre_balneario']); ?>
                </p>
            </div>
            <a href="balneario.php?id=<?php echo $boletin['id_balneario']; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
        </div>

        <!-- Formulario de edición -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form id="formEditarBoletin" action="../../../controllers/super/boletines/actualizar.php" method="POST">
                            <input type="hidden" name="id_boletin" value="<?php echo $id_boletin; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Título del Boletín</label>
                                <input type="text" class="form-control" name="titulo_boletin" 
                                       value="<?php echo htmlspecialchars($boletin['titulo_boletin']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contenido</label>
                                <textarea class="form-control" name="contenido_boletin" rows="10" required><?php 
                                    echo htmlspecialchars($boletin['contenido_boletin']); 
                                ?></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" name="accion" value="borrador" class="btn btn-secondary">
                                    <i class="bi bi-save me-2"></i>Guardar como Borrador
                                </button>
                                <button type="submit" name="accion" value="enviar" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Enviar Boletín
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Vista previa -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Vista Previa</h5>
                        <div class="preview-content">
                            <h5 id="previewTitulo"></h5>
                            <div id="previewContenido"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

        // Actualizar vista previa
        function actualizarVistaPrevia() {
            $('#previewTitulo').text($('input[name="titulo_boletin"]').val());
            $('#previewContenido').html($('textarea[name="contenido_boletin"]').val().replace(/\n/g, '<br>'));
        }

        // Eventos para actualizar vista previa
        $('input[name="titulo_boletin"], textarea[name="contenido_boletin"]').on('input', actualizarVistaPrevia);

        // Inicializar vista previa
        actualizarVistaPrevia();

        // Manejar checkbox de borrador
        $('#checkBorrador').on('change', function() {
            const destinatarios = $('input[name="destinatarios[]"]');
            if (this.checked) {
                destinatarios.prop('required', false);
            } else {
                destinatarios.prop('required', true);
            }
        });

        // Manejar envío del formulario
        $('#formEditarBoletin').on('submit', function(e) {
            e.preventDefault();

            // Validar destinatarios si no es borrador
            if (!$('#checkBorrador').prop('checked') && 
                !$('input[name="destinatarios[]"]:checked').length) {
                toastr.error('Debe seleccionar al menos un tipo de destinatario');
                return;
            }

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
                        window.location.href = 'balneario.php?id=<?php echo $boletin['id_balneario']; ?>';
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