<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once 'PromocionController.php';

session_start();

// Verificar autenticación
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$auth->checkAuth();
$auth->checkRole(['administrador_balneario']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $promocionController = new PromocionController($db);
    
    // Preparar datos de la promoción
    $datos = [
        'id_balneario' => $_SESSION['id_balneario'],
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin']
    ];

    // Validar fechas
    if (strtotime($datos['fecha_fin']) < strtotime($datos['fecha_inicio'])) {
        header('Location: ../../../views/balneario/promociones/lista.php?error=' . urlencode('La fecha de fin no puede ser anterior a la fecha de inicio'));
        exit();
    }

    // Intentar crear la promoción
    $resultado = $promocionController->crearPromocion($datos);

    if ($resultado === true) {
        header('Location: ../../../views/balneario/promociones/lista.php?success=' . urlencode('Promoción creada exitosamente'));
    } else {
        header('Location: ../../../views/balneario/promociones/lista.php?error=' . urlencode($resultado));
    }
    exit();
}

header('Location: ../../../views/balneario/promociones/lista.php');
exit();
?>