<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './UsuarioSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    if (!isset($_POST['id_usuario'])) {
        throw new Exception('ID de usuario no proporcionado');
    }

    $usuarioController = new UsuarioSuperController($db);
    $resultado = $usuarioController->regenerarPassword($_POST['id_usuario']);

    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña regenerada exitosamente',
            'password' => $resultado['password']
        ]);
    } else {
        throw new Exception($resultado['message']);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 