<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once '../../../controllers/balneario/EventoController.php';

// Verificar autenticación
session_start();
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$auth->checkAuth();
$auth->checkRole(['administrador_balneario']);

// Obtener datos del evento
$id_evento = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$eventoController = new EventoController($db);
$evento = $eventoController->obtenerEvento($id_evento);

// Verificar que el evento existe y pertenece al balneario
if (!$evento || $evento['id_balneario'] != $_SESSION['id_balneario']) {
    header('Location: lista.php?error=' . urlencode('Evento no encontrado'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-calendar-event me-2"></i>Editar Evento</h2>
            <a href="lista.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
        </div>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="../../controllers/eventos/actualizar.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_evento" value="<?php echo $evento['id_evento']; ?>">
                    <input type="hidden" name="id_balneario" value="<?php echo $_SESSION['id_balneario']; ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Título del Evento</label>
                            <input type="text" class="form-control" name="titulo" 
                                   value="<?php echo htmlspecialchars($evento['titulo_evento']); ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="4" required><?php 
                                echo htmlspecialchars($evento['descripcion_evento']); 
                            ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" 
                                   value="<?php echo $evento['fecha_inicio_evento']; ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" 
                                   value="<?php echo $evento['fecha_fin_evento']; ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Imagen del Evento</label>
                            <?php if ($evento['url_imagen_evento']): ?>
                            <div class="mb-2">
                                <img src="../../<?php echo htmlspecialchars($evento['url_imagen_evento']); ?>" 
                                     class="img-thumbnail" style="max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="imagen" accept="image/*">
                            <div class="form-text">
                                Formatos permitidos: JPG, PNG. Tamaño máximo: 2MB. 
                                Dejar en blanco para mantener la imagen actual.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de fechas
        document.querySelector('input[name="fecha_fin"]').addEventListener('change', function() {
            var fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
            var fechaFin = this.value;
            
            if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
                alert('La fecha de fin no puede ser anterior a la fecha de inicio');
                this.value = '';
            }
        });
    </script>
</body>
</html>