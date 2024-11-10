<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once '../../../controllers/balneario/promociones/PromocionController.php';

// Verificar autenticación
session_start();
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$auth->checkAuth();
$auth->checkRole(['administrador_balneario']);

// Obtener datos de la promoción
$id_promocion = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$promocionController = new PromocionController($db);
$promocion = $promocionController->obtenerPromocion($id_promocion);

// Verificar que la promoción existe y pertenece al balneario
if (!$promocion || $promocion['id_balneario'] != $_SESSION['id_balneario']) {
    header('Location: lista.php?error=' . urlencode('Promoción no encontrada'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Promoción</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-tag me-2"></i>Editar Promoción</h2>
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
                <form action="../../../controllers/balneario/promociones/actualizarPromocion.php" method="POST">
                    <input type="hidden" name="id_promocion" value="<?php echo $promocion['id_promocion']; ?>">
                    <input type="hidden" name="id_balneario" value="<?php echo $_SESSION['id_balneario']; ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Título de la Promoción</label>
                            <input type="text" class="form-control" name="titulo" 
                                   value="<?php echo htmlspecialchars($promocion['titulo_promocion']); ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="4" required><?php 
                                echo htmlspecialchars($promocion['descripcion_promocion']); 
                            ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" 
                                   value="<?php echo $promocion['fecha_inicio_promocion']; ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" 
                                   value="<?php echo $promocion['fecha_fin_promocion']; ?>" required>
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