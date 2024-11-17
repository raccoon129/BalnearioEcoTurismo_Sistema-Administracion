<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../globalControllers/EmailController.php';
require_once __DIR__ . '/BoletinSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Validar datos recibidos
    $postData = json_decode(file_get_contents('php://input'), true);
    if (!isset($postData['id_boletin']) || !isset($postData['destinatarios'])) {
        throw new Exception('Datos incompletos para el envío del boletín');
    }

    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());
    $emailController = new EmailController($db);

    // Obtener información del boletín
    $boletin = $boletinController->obtenerBoletin($postData['id_boletin']);
    if (!$boletin) {
        throw new Exception('Boletín no encontrado en el sistema');
    }

    if ($boletin['estado_boletin'] === 'enviado') {
        throw new Exception('Este boletín ya ha sido enviado anteriormente');
    }

    // Validar destinatarios
    $destinatarios = $postData['destinatarios'];
    if (empty($destinatarios)) {
        throw new Exception('No se proporcionaron destinatarios para el envío');
    }

    // Enviar el boletín
    $resultado = $emailController->enviarBoletinMasivo(
        $boletin['titulo_boletin'],
        $boletin['contenido_boletin'],
        $destinatarios
    );

    if ($resultado['success']) {
        // Actualizar estado del boletín
        $boletinController->actualizarEstado($postData['id_boletin'], 'enviado');
        
        echo json_encode([
            'success' => true,
            'message' => 'Boletín enviado exitosamente',
            'detalles' => $resultado
        ]);
    } else {
        throw new Exception($resultado['message']);
    }

} catch (Exception $e) {
    error_log('Error en enviar_boletin_superadmin.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?> 