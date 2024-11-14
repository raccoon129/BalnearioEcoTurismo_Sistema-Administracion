<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './PromocionSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    if (!isset($_POST['id_promocion'])) {
        throw new Exception('ID de promoción no proporcionado');
    }

    $promocionController = new PromocionSuperController($db);
    
    // Verificar si la promoción existe y puede ser eliminada
    if (!$promocionController->puedeEditarPromocion($_POST['id_promocion'])) {
        throw new Exception('No se puede eliminar una promoción que ya ha finalizado');
    }

    $resultado = $promocionController->eliminarPromocion($_POST['id_promocion']);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Promoción eliminada exitosamente'
        ]);
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    error_log('Error en eliminar.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 