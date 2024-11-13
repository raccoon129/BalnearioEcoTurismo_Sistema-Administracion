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
        'precio_general'
    ];

    foreach ($campos_requeridos as $campo) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            throw new Exception("El campo $campo es requerido");
        }
    }

    // Preparar datos para actualizar
    $datos = [
        'nombre_balneario' => $_POST['nombre_balneario'],
        'descripcion_balneario' => $_POST['descripcion_balneario'],
        'direccion_balneario' => $_POST['direccion_balneario'],
        'horario_apertura' => $_POST['horario_apertura'],
        'horario_cierre' => $_POST['horario_cierre'],
        'telefono_balneario' => $_POST['telefono_balneario'],
        'email_balneario' => $_POST['email_balneario'],
        'facebook_balneario' => $_POST['facebook_balneario'] ?? null,
        'instagram_balneario' => $_POST['instagram_balneario'] ?? null,
        'x_balneario' => $_POST['x_balneario'] ?? null,
        'tiktok_balneario' => $_POST['tiktok_balneario'] ?? null,
        'precio_general' => $_POST['precio_general']
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
        'message' => $e->getMessage()
    ]);
}
?> 