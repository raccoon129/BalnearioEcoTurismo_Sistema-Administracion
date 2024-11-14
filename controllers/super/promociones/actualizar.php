<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './PromocionSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Validar ID de promoción
    if (!isset($_POST['id_promocion'])) {
        throw new Exception('ID de promoción no proporcionado');
    }

    // Validar campos requeridos
    $camposRequeridos = [
        'titulo_promocion' => 'Título',
        'descripcion_promocion' => 'Descripción',
        'fecha_inicio_promocion' => 'Fecha de inicio',
        'fecha_fin_promocion' => 'Fecha de fin',
        'id_balneario' => 'Balneario'
    ];

    foreach ($camposRequeridos as $campo => $nombre) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            throw new Exception("El campo {$nombre} es requerido");
        }
    }

    // Validar fechas
    $fechaInicio = strtotime($_POST['fecha_inicio_promocion']);
    $fechaFin = strtotime($_POST['fecha_fin_promocion']);

    if ($fechaInicio === false || $fechaFin === false) {
        throw new Exception('Formato de fecha inválido');
    }

    if ($fechaFin <= $fechaInicio) {
        throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
    }

    // Verificar que el balneario existe
    $query = "SELECT id_balneario FROM balnearios WHERE id_balneario = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $_POST['id_balneario']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('El balneario seleccionado no existe');
    }

    // Preparar datos
    $datos = [
        'titulo_promocion' => trim($_POST['titulo_promocion']),
        'descripcion_promocion' => trim($_POST['descripcion_promocion']),
        'fecha_inicio_promocion' => $_POST['fecha_inicio_promocion'],
        'fecha_fin_promocion' => $_POST['fecha_fin_promocion'],
        'id_balneario' => $_POST['id_balneario']
    ];

    $promocionController = new PromocionSuperController($db);
    $resultado = $promocionController->actualizarPromocion($_POST['id_promocion'], $datos);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Promoción actualizada exitosamente'
        ]);
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    error_log('Error en actualizar.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 