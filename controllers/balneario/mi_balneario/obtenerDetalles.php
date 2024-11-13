<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BalnearioController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    $balnearioController = new BalnearioController($db);
    $balneario = $balnearioController->obtenerBalneario($_SESSION['id_balneario']);

    if ($balneario) {
        echo json_encode([
            'success' => true,
            'data' => $balneario
        ]);
    } else {
        throw new Exception('No se encontró el balneario');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 