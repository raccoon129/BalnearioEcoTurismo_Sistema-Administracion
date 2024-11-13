<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './ReservacionController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    // Obtener fecha del parámetro o usar la fecha actual
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

    $reservacionController = new ReservacionController($db);
    $resumen = $reservacionController->obtenerResumenPorFecha($_SESSION['id_balneario'], $fecha);

    echo json_encode([
        'success' => true,
        'data' => $resumen
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 