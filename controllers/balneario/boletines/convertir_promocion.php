<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinController.php';
require_once '../promociones/PromocionController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    // Obtener el ID del usuario autenticado
    $id_usuario = $auth->getUsuarioId();

    // Verificar datos recibidos
    if (!isset($_POST['id_promocion']) || !isset($_POST['titulo_boletin']) || !isset($_POST['contenido_boletin'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $id_promocion = filter_var($_POST['id_promocion'], FILTER_VALIDATE_INT);
    if (!$id_promocion) {
        throw new Exception('ID de promoción inválido');
    }

    // Verificar que la promoción pertenece al balneario
    $promocionController = new PromocionController($db);
    $promocion = $promocionController->obtenerPromocion($id_promocion);

    if (!$promocion || $promocion['id_balneario'] != $_SESSION['id_balneario']) {
        throw new Exception('No tiene permiso para convertir esta promoción');
    }

    // Crear el boletín usando el ID del usuario autenticado
    $boletinController = new BoletinController($db);
    $resultado = $boletinController->crearBoletin(
        $_POST['titulo_boletin'],
        $_POST['contenido_boletin'],
        $id_usuario,
        true // es_borrador = true
    );

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Promoción convertida a boletín exitosamente'
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