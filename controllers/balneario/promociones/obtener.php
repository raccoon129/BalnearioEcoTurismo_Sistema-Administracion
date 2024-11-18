<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './PromocionController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if (!isset($_GET['id_promocion'])) {
        throw new Exception('ID de promoción no proporcionado');
    }

    $promocionController = new PromocionController($db);
    $promocion = $promocionController->obtenerPromocion($_GET['id_promocion']);

    if (!$promocion) {
        throw new Exception('Promoción no encontrada');
    }

    if ($promocion['id_balneario'] != $_SESSION['id_balneario']) {
        throw new Exception('No tiene permiso para acceder a esta promoción');
    }

    echo json_encode([
        'success' => true,
        'data' => $promocion
    ]);

} catch (Exception $e) {
    error_log('Error en obtener.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 