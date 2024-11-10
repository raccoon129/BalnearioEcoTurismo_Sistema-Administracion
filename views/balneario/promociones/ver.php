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

// Determinar el estado de la promoción
$hoy = strtotime('today');
$inicio = strtotime($promocion['fecha_inicio_promocion']);
$fin = strtotime($promocion['fecha_fin_promocion']);

if ($hoy < $inicio) {
    $estado = ['texto' => 'Próxima', 'clase' => 'bg-info'];
} elseif ($hoy > $fin) {
    $estado = ['texto' => 'Finalizada', 'clase' => 'bg-secondary'];
} else {
    $estado = ['texto' => 'Activa', 'clase' => 'bg-success'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Promoción</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .promotion-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .date-badge {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .description-box {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-tag-fill me-2"></i>Detalles de la Promoción</h2>
            <a href="lista.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver a la lista
            </a>
        </div>

        <div class="promotion-header shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 mb-3"><?php echo htmlspecialchars($promocion['titulo_promocion']); ?></h1>
                    <span class="badge <?php echo $estado['clase']; ?> mb-3">
                        <?php echo $estado['texto']; ?>
                    </span>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-flex flex-column gap-2">
                        <div class="date-badge">
                            <i class="bi bi-calendar-event me-2"></i>
                            <strong>Inicio:</strong> <?php echo date('d/m/Y', strtotime($promocion['fecha_inicio_promocion'])); ?>
                        </div>
                        <div class="date-badge">
                            <i class="bi bi-calendar-check me-2"></i>
                            <strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($promocion['fecha_fin_promocion'])); ?>
                        </div>
                        <?php
                        // Calcular días restantes
                        $hoy = strtotime('today');
                        $fin = strtotime($promocion['fecha_fin_promocion']);
                        $dias_restantes = ceil(($fin - $hoy) / (60 * 60 * 24));
                        
                        if ($dias_restantes < 0) {
                            // Si la promoción ya finalizó
                            echo '<div class="date-badge text-danger">';
                            echo '<i class="bi bi-exclamation-circle me-2"></i>';
                            echo '<strong>Ya ha finalizado</strong>';
                            echo '</div>';
                        } elseif ($dias_restantes == 0) {
                            // Si finaliza hoy
                            echo '<div class="date-badge text-warning">';
                            echo '<i class="bi bi-clock me-2"></i>';
                            echo '<strong>Finaliza hoy</strong>';
                            echo '</div>';
                        } else {
                            // Si aún está vigente o es futura
                            $clase = ($dias_restantes <= 5) ? 'text-warning' : 'text-success';
                            echo '<div class="date-badge ' . $clase . '">';
                            echo '<i class="bi bi-clock-history me-2"></i>';
                            echo '<strong>Días restantes:</strong> ' . $dias_restantes;
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="description-box">
            <h3 class="mb-4"><i class="bi bi-info-circle me-2"></i>Descripción</h3>
            <p class="lead">
                <?php echo nl2br(htmlspecialchars($promocion['descripcion_promocion'])); ?>
            </p>
        </div>

        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="editar.php?id=<?php echo $promocion['id_promocion']; ?>" class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>Editar Promoción
            </a>
            <button class="btn btn-success" onclick="convertirABoletin(<?php echo $promocion['id_promocion']; ?>)">
                <i class="bi bi-envelope me-2"></i>Convertir a Boletín
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function convertirABoletin(id) {
            if (confirm('¿Desea crear un boletín con la información de esta promoción?')) {
                window.location.href = `convertir_boletin.php?id=${id}`;
            }
        }
    </script>
</body>
</html> 