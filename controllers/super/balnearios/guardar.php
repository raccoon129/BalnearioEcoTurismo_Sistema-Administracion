<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BalnearioSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación y permisos
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Validar campos requeridos
    $camposRequeridos = [
        'nombre_balneario' => 'Nombre del balneario',
        'descripcion_balneario' => 'Descripción',
        'direccion_balneario' => 'Dirección',
        'horario_apertura' => 'Horario de apertura',
        'horario_cierre' => 'Horario de cierre',
        'telefono_balneario' => 'Teléfono',
        'email_balneario' => 'Email',
        'precio_general' => 'Precio general'
    ];

    foreach ($camposRequeridos as $campo => $nombre) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            throw new Exception("El campo {$nombre} es requerido");
        }
    }

    // Preparar datos para inserción
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

    // Validaciones específicas
    // Validar formato de email
    if (!filter_var($datos['email_balneario'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }

    // Validar formato de teléfono (10 dígitos)
    if (!preg_match('/^\d{10}$/', $datos['telefono_balneario'])) {
        throw new Exception('El teléfono debe tener exactamente 10 dígitos');
    }

    // Validar precio
    if ($datos['precio_general'] <= 0) {
        throw new Exception('El precio general debe ser mayor a 0');
    }

    // Validar horarios
    if ($datos['horario_cierre'] <= $datos['horario_apertura']) {
        throw new Exception('El horario de cierre debe ser posterior al horario de apertura');
    }

    // Validar URLs de redes sociales si están presentes
    $redesSociales = ['facebook_balneario', 'instagram_balneario', 'x_balneario', 'tiktok_balneario'];
    foreach ($redesSociales as $red) {
        if (!empty($datos[$red]) && !filter_var($datos[$red], FILTER_VALIDATE_URL)) {
            throw new Exception("La URL de {$red} no es válida");
        }
    }

    // Crear el balneario
    $balnearioController = new BalnearioSuperController($db);
    $resultado = $balnearioController->crearBalneario($datos);

    // Verificar resultado
    if (is_numeric($resultado)) { // Si devuelve el ID del balneario
        echo json_encode([
            'success' => true,
            'message' => 'Balneario creado exitosamente',
            'id_balneario' => $resultado
        ]);
    } else {
        throw new Exception($resultado); // Si devuelve un mensaje de error
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 