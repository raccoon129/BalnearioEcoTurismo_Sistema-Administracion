<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinController.php';

header('Content-Type: application/json');
session_start();

try {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if (!isset($_GET['id_boletin'])) {
        throw new Exception('ID de boletín no proporcionado');
    }

    $id_boletin = (int)$_GET['id_boletin'];
    $boletinController = new BoletinController($db);
    $boletin = $boletinController->obtenerBoletin($id_boletin, $_SESSION['id_balneario']);

    if (!$boletin) {
        throw new Exception('Boletín no encontrado');
    }

    echo json_encode([
        'success' => true,
        'data' => $boletin
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 