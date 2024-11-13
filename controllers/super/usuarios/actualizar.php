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

    // Log para debugging
    error_log('POST data recibida: ' . print_r($_POST, true));

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

    // Validar rol y asignación de balneario
    $rol = $_POST['rol_usuario'];
    $idBalneario = !empty($_POST['id_balneario']) ? $_POST['id_balneario'] : null;

    if ($rol === 'administrador_balneario') {
        if (empty($idBalneario)) {
            throw new Exception('Un administrador de balneario debe tener un balneario asignado');
        }

        // Verificar que el balneario existe
        $query = "SELECT id_balneario FROM balnearios WHERE id_balneario = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $idBalneario);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception('El balneario seleccionado no existe');
        }
    } else if ($rol === 'superadministrador') {
        // Forzar NULL para superadministradores
        $idBalneario = null;
    } else {
        throw new Exception('Rol no válido');
    }

    // Preparar datos
    $datos = [
        'nombre_usuario' => trim($_POST['nombre_usuario']),
        'email_usuario' => trim($_POST['email_usuario']),
        'rol_usuario' => $rol,
        'id_balneario' => $idBalneario
    ];

    // Log para debugging
    error_log('Datos a actualizar: ' . print_r($datos, true));

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
    error_log('Error en actualizar.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(), // Para debugging
        'post_data' => $_POST // Para debugging
    ]);
}
?> 