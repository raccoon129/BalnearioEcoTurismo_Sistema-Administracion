<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if (!isset($_POST['id_boletin'])) {
        throw new Exception('ID de boletín no proporcionado');
    }

    $boletinController = new BoletinController($db);
    $resultado = $boletinController->enviarBoletin($_POST['id_boletin'], $_SESSION['id_balneario']);

    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Boletín enviado exitosamente',
            'detalles' => $resultado
        ]);
    } else {
        throw new Exception($resultado['message']);
    }

} catch (Exception $e) {
    error_log('Error en enviar.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 