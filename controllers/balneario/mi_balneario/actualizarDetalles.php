<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BalnearioController.php';

session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    // Validar que se recibieron todos los campos requeridos
    $campos_requeridos = [
        'nombre_balneario',
        'descripcion_balneario',
        'direccion_balneario',
        'horario_apertura',
        'horario_cierre',
        'telefono_balneario',
        'email_balneario',
        'precio_general_adultos',
        'precio_general_infantes'
    ];

    foreach ($campos_requeridos as $campo) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            throw new Exception("El campo {$campo} es requerido");
        }
    }

    // Log para debugging
    error_log("POST data recibida: " . print_r($_POST, true));

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
        'precio_general_adultos' => (float)$_POST['precio_general_adultos'],
        'precio_general_infantes' => (float)$_POST['precio_general_infantes']
    ];

    $balnearioController = new BalnearioController($db);
    
    // Validar datos antes de actualizar
    $errores = $balnearioController->validarDatos($datos);
    if (!empty($errores)) {
        throw new Exception(implode("\n", $errores));
    }

    // Actualizar balneario
    $resultado = $balnearioController->actualizarBalneario($_SESSION['id_balneario'], $datos);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Los datos del balneario han sido actualizados exitosamente'
        ]);
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'post_data' => $_POST
        ]
    ]);
}
?> 