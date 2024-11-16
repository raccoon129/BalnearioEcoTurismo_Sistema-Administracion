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

    // Validar datos recibidos
    if (!isset($_POST['id_boletin']) || !isset($_POST['destinatarios'])) {
        throw new Exception('Datos incompletos');
    }

    $id_boletin = (int)$_POST['id_boletin'];
    $destinatarios = $_POST['destinatarios'];

    // Obtener información del boletín
    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());
    $boletin = $boletinController->obtenerDetallesBoletinSuperAdmin($id_boletin);

    if (!$boletin || $boletin['estado_boletin'] !== 'borrador') {
        throw new Exception('Boletín no encontrado o no es un borrador');
    }

    // Inicializar mailer
    $mailConfig = new MailConfig($db);
    $destinatariosCorreo = [];

    // Obtener correos según los tipos seleccionados
    foreach ($destinatarios as $tipo) {
        switch ($tipo) {
            case 'superadmin':
                $superadmins = $boletinController->obtenerCorreosSuperAdmin();
                $destinatariosCorreo = array_merge($destinatariosCorreo, $superadmins);
                break;

            case 'admin':
                $admins = $boletinController->obtenerCorreosAdminBalnearios();
                $destinatariosCorreo = array_merge($destinatariosCorreo, $admins);
                break;

            case 'suscriptores':
                $suscriptores = $boletinController->obtenerCorreosSuscriptores();
                $destinatariosCorreo = array_merge($destinatariosCorreo, $suscriptores);
                break;
        }
    }

    // Eliminar duplicados
    $destinatariosCorreo = array_unique($destinatariosCorreo, SORT_REGULAR);

    if (empty($destinatariosCorreo)) {
        throw new Exception('No se encontraron destinatarios para el envío');
    }

    // Enviar el boletín
    $resultado = $mailConfig->enviarBoletin(
        null, // No hay id_balneario para boletines del sistema
        $boletin['titulo_boletin'],
        $boletin['contenido_boletin']
    );

    if ($resultado['success']) {
        // Actualizar estado del boletín
        $boletinController->actualizarEstado($id_boletin, 'enviado');

        echo json_encode([
            'success' => true,
            'message' => 'Boletín enviado exitosamente',
            'detalles' => [
                'total_destinatarios' => count($destinatariosCorreo),
                'enviados' => $resultado['enviados']
            ]
        ]);
    } else {
        throw new Exception($resultado['message']);
    }

} catch (Exception $e) {
    error_log('Error en enviar_boletin_superadmin.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 