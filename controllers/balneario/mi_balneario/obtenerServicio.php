<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './ServicioController.php';

header('Content-Type: application/json');
session_start();

try {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if (!isset($_GET['id_servicio'])) {
        throw new Exception('ID de servicio no proporcionado');
    }

    $servicioController = new ServicioController($db);
    $servicio = $servicioController->obtenerServicio($_GET['id_servicio'], $_SESSION['id_balneario']);

    if ($servicio) {
        echo json_encode([
            'success' => true,
            'data' => $servicio
        ]);
    } else {
        throw new Exception('Servicio no encontrado');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 