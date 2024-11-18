<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './EventoController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if (!isset($_GET['id_evento'])) {
        throw new Exception('ID de evento no proporcionado');
    }

    $eventoController = new EventoController($db);
    $evento = $eventoController->obtenerEvento($_GET['id_evento']);

    if (!$evento) {
        throw new Exception('Evento no encontrado');
    }

    if ($evento['id_balneario'] != $_SESSION['id_balneario']) {
        throw new Exception('No tiene permiso para acceder a este evento');
    }

    echo json_encode([
        'success' => true,
        'data' => $evento
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