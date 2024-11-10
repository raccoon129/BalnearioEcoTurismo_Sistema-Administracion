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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_promocion'])) {
    $promocionController = new PromocionController($db);
    
    // Verificar que la promoción pertenece al balneario del usuario
    if (!$promocionController->verificarPertenencia($_POST['id_promocion'], $_SESSION['id_balneario'])) {
        header('Location: ../../../views/balneario/promociones/lista.php?error=' . urlencode('No tiene permiso para editar esta promoción'));
        exit();
    }

    // Preparar datos de la promoción
    $datos = [
        'id_promocion' => $_POST['id_promocion'],
        'id_balneario' => $_SESSION['id_balneario'],
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin']
    ];

    // Validar fechas
    if (strtotime($datos['fecha_fin']) < strtotime($datos['fecha_inicio'])) {
        header('Location: ../../../views/balneario/promociones/editar.php?id=' . $_POST['id_promocion'] . '&error=' . urlencode('La fecha de fin no puede ser anterior a la fecha de inicio'));
        exit();
    }

    // Intentar actualizar la promoción
    $resultado = $promocionController->actualizarPromocion($datos);

    if ($resultado === true) {
        header('Location: ../../../views/balneario/promociones/lista.php?success=' . urlencode('Promoción actualizada exitosamente'));
    } else {
        header('Location: ../../../views/balneario/promociones/editar.php?id=' . $_POST['id_promocion'] . '&error=' . urlencode($resultado));
    }
    exit();
}

header('Location: ../../../views/balneario/promociones/lista.php');
exit();
?>