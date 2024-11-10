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
        header('Location: ../../../views/balneario/promociones/lista.php?error=' . urlencode('No tiene permiso para eliminar esta promoción'));
        exit();
    }

    // Intentar eliminar la promoción
    $resultado = $promocionController->eliminarPromocion($_POST['id_promocion'], $_SESSION['id_balneario']);

    if ($resultado === true) {
        header('Location: ../../../views/balneario/promociones/lista.php?success=' . urlencode('Promoción eliminada exitosamente'));
    } else {
        header('Location: ../../../views/balneario/promociones/lista.php?error=' . urlencode($resultado));
    }
    exit();
}

header('Location: ../../../views/balneario/promociones/lista.php');
exit();
?>