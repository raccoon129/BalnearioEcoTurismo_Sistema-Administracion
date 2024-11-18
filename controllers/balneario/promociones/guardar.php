<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './PromocionController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    // Validar campos requeridos
    if (!isset($_POST['titulo']) || !isset($_POST['descripcion']) || 
        !isset($_POST['fecha_inicio']) || !isset($_POST['fecha_fin'])) {
        throw new Exception('Todos los campos son requeridos');
    }

    // Preparar datos
    $datos = [
        'id_balneario' => $_SESSION['id_balneario'],
        'titulo' => trim($_POST['titulo']),
        'descripcion' => trim($_POST['descripcion']),
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin']
    ];

    // Validar fechas
    if (strtotime($datos['fecha_fin']) < strtotime($datos['fecha_inicio'])) {
        throw new Exception('La fecha de fin no puede ser anterior a la fecha de inicio');
    }

    // Crear la promoción
    $promocionController = new PromocionController($db);
    $resultado = $promocionController->crearPromocion($datos);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Promoción creada exitosamente'
        ]);
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    error_log("Error en guardar.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 