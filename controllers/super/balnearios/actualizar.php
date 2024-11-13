<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BalnearioSuperController.php';

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
    if (!isset($_POST['id_balneario'])) {
        throw new Exception('ID de balneario no proporcionado');
    }

    // Preparar datos para actualización
    $datos = [
        'nombre_balneario' => trim($_POST['nombre_balneario']),
        'descripcion_balneario' => trim($_POST['descripcion_balneario']),
        'direccion_balneario' => trim($_POST['direccion_balneario']),
        'horario_apertura' => $_POST['horario_apertura'],
        'horario_cierre' => $_POST['horario_cierre'],
        'telefono_balneario' => trim($_POST['telefono_balneario']),
        'email_balneario' => trim($_POST['email_balneario']),
        'facebook_balneario' => trim($_POST['facebook_balneario'] ?? ''),
        'instagram_balneario' => trim($_POST['instagram_balneario'] ?? ''),
        'x_balneario' => trim($_POST['x_balneario'] ?? ''),
        'tiktok_balneario' => trim($_POST['tiktok_balneario'] ?? ''),
        'precio_general' => (float)$_POST['precio_general']
    ];

    // Validaciones adicionales
    if (empty($datos['nombre_balneario'])) {
        throw new Exception('El nombre del balneario es requerido');
    }

    if (!filter_var($datos['email_balneario'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El correo electrónico no es válido');
    }

    if (!preg_match('/^\d{10}$/', $datos['telefono_balneario'])) {
        throw new Exception('El teléfono debe tener 10 dígitos');
    }

    if ($datos['precio_general'] < 0) {
        throw new Exception('El precio no puede ser negativo');
    }

    // Actualizar balneario
    $balnearioController = new BalnearioSuperController($db);
    $resultado = $balnearioController->actualizarBalneario($_POST['id_balneario'], $datos);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Balneario actualizado exitosamente'
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