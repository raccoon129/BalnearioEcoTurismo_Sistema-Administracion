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
    if (!isset($_POST['nombre_servicio']) || !isset($_POST['descripcion_servicio']) || !isset($_POST['precio_adicional'])) {
        throw new Exception('Todos los campos son requeridos');
    }

    // Validar precio
    $precio = filter_var($_POST['precio_adicional'], FILTER_VALIDATE_FLOAT);
    if ($precio === false || $precio < 0) {
        throw new Exception('El precio debe ser un número válido y positivo');
    }

    // Formatear precio a dos decimales
    $precio = number_format($precio, 2, '.', '');

    $datos = [
        'nombre_servicio' => trim($_POST['nombre_servicio']),
        'descripcion_servicio' => trim($_POST['descripcion_servicio']),
        'precio_adicional' => $precio
    ];

    // Validar que los campos no estén vacíos
    if (empty($datos['nombre_servicio']) || empty($datos['descripcion_servicio'])) {
        throw new Exception('Todos los campos son requeridos');
    }

    $servicioController = new ServicioController($db);
    
    // Si existe ID, actualizar, si no, crear nuevo
    if (isset($_POST['id_servicio']) && !empty($_POST['id_servicio'])) {
        $resultado = $servicioController->actualizarServicio(
            $_POST['id_servicio'],
            $datos,
            $_SESSION['id_balneario']
        );
        $mensaje = 'Servicio actualizado exitosamente';
    } else {
        $resultado = $servicioController->guardarServicio(
            $datos,
            $_SESSION['id_balneario']
        );
        $mensaje = 'Servicio guardado exitosamente';
    }

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => $mensaje
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