<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    if (!isset($_GET['id_boletin'])) {
        throw new Exception('ID de boletín no proporcionado');
    }

    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());
    $boletin = $boletinController->obtenerBoletin($_GET['id_boletin']);

    if (!$boletin) {
        throw new Exception('Boletín no encontrado');
    }

    echo json_encode([
        'success' => true,
        'data' => $boletin
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