<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './ServicioController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    // Validar datos recibidos
    if (!isset($_POST['id_servicio']) || !isset($_POST['nombre_servicio']) || 
        !isset($_POST['descripcion_servicio']) || !isset($_POST['precio_adicional'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $servicioController = new ServicioController($db);
    $resultado = $servicioController->actualizarServicio(
        $_POST['id_servicio'],
        $_POST,
        $_SESSION['id_balneario']
    );

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Servicio actualizado exitosamente'
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