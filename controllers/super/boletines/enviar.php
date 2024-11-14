<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once '../../../config/mail.php';
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

    if (!isset($_POST['id_boletin'])) {
        throw new Exception('ID de boletín no proporcionado');
    }

    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());
    $mailConfig = new MailConfig($db);

    // Obtener información del boletín
    $boletin = $boletinController->obtenerBoletin($_POST['id_boletin']);
    if (!$boletin) {
        throw new Exception('Boletín no encontrado');
    }

    // Obtener destinatarios según el tipo
    $destinatarios = [];
    foreach ($_POST['destinatarios'] as $tipo) {
        $destinatarios = array_merge(
            $destinatarios, 
            $boletinController->obtenerDestinatarios($tipo, $boletin['id_balneario'])
        );
    }

    if (empty($destinatarios)) {
        throw new Exception('No se encontraron destinatarios para el envío');
    }

    // Enviar el boletín
    $resultado = $mailConfig->enviarBoletin(
        $boletin['id_balneario'],
        $boletin['titulo_boletin'],
        $boletin['contenido_boletin']
    );

    if ($resultado['success']) {
        // Actualizar estado del boletín
        $boletinController->actualizarEstado($_POST['id_boletin'], 'enviado');

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