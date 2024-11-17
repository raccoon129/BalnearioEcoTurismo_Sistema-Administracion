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

    if (!isset($_GET['tipo'])) {
        throw new Exception('Tipo de destinatario no especificado');
    }

    $boletinController = new BoletinSuperController($db, $auth->getUsuarioId());
    $destinatarios = [];

    switch ($_GET['tipo']) {
        case 'superadmin':
            $destinatarios = $boletinController->obtenerCorreosSuperAdmin();
            break;
        case 'admin':
            $destinatarios = $boletinController->obtenerCorreosAdminBalnearios();
            break;
        case 'suscriptores':
            $destinatarios = $boletinController->obtenerCorreosSuscriptores();
            break;
        default:
            throw new Exception('Tipo de destinatario no válido');
    }

    echo json_encode([
        'success' => true,
        'tipo' => $_GET['tipo'],
        'destinatarios' => $destinatarios,
        'total' => count($destinatarios)
    ]);

} catch (Exception $e) {
    error_log('Error en obtener_destinatarios.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 