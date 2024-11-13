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

    // Validar ID de usuario
    if (!isset($_POST['id_usuario'])) {
        throw new Exception('ID de usuario no proporcionado');
    }

    // Validar campos requeridos
    $camposRequeridos = [
        'nombre_usuario' => 'Nombre',
        'email_usuario' => 'Email',
        'rol_usuario' => 'Rol'
    ];

    foreach ($camposRequeridos as $campo => $nombre) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            throw new Exception("El campo {$nombre} es requerido");
        }
    }

    // Validar email
    if (!filter_var($_POST['email_usuario'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del email no es válido');
    }

    // Validar que si es administrador_balneario tenga un balneario asignado
    if ($_POST['rol_usuario'] === 'administrador_balneario' && empty($_POST['id_balneario'])) {
        throw new Exception('Debe seleccionar un balneario para el administrador');
    }

    // Preparar datos
    $datos = [
        'nombre_usuario' => trim($_POST['nombre_usuario']),
        'email_usuario' => trim($_POST['email_usuario']),
        'rol_usuario' => $_POST['rol_usuario'],
        'id_balneario' => $_POST['id_balneario'] ?? null
    ];

    $usuarioController = new UsuarioSuperController($db);
    $resultado = $usuarioController->actualizarUsuario($_POST['id_usuario'], $datos);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ]);
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 