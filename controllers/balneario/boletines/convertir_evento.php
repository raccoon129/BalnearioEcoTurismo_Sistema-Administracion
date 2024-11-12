<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinController.php';
require_once '../eventos/EventoController.php';

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
    if (!isset($_POST['id_evento']) || !isset($_POST['titulo_boletin']) || !isset($_POST['contenido_boletin'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $id_evento = filter_var($_POST['id_evento'], FILTER_VALIDATE_INT);
    if (!$id_evento) {
        throw new Exception('ID de evento inválido');
    }

    // Verificar que el evento pertenece al balneario
    $eventoController = new EventoController($db);
    $evento = $eventoController->obtenerEvento($id_evento);

    if (!$evento || $evento['id_balneario'] != $_SESSION['id_balneario']) {
        throw new Exception('No tiene permiso para convertir este evento');
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
            'message' => 'Evento convertido a boletín exitosamente'
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