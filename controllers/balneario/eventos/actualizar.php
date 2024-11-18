<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './EventoController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if (!isset($_POST['id_evento'])) {
        throw new Exception('ID de evento no proporcionado');
    }

    $eventoController = new EventoController($db);
    
    // Verificar que el evento pertenece al balneario
    if (!$eventoController->verificarPertenencia($_POST['id_evento'], $_SESSION['id_balneario'])) {
        throw new Exception('No tiene permiso para editar este evento');
    }

    // Preparar datos del evento
    $datos = [
        'id_evento' => $_POST['id_evento'],
        'id_balneario' => $_SESSION['id_balneario'],
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin']
    ];

    // Validar fechas
    if (strtotime($datos['fecha_fin']) < strtotime($datos['fecha_inicio'])) {
        throw new Exception('La fecha de fin no puede ser anterior a la fecha de inicio');
    }

    // Procesar imagen si se proporcionó una nueva
    $imagen = isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK ? $_FILES['imagen'] : null;

    // Log para debugging
    error_log('Datos recibidos: ' . print_r($datos, true));
    error_log('Imagen recibida: ' . ($imagen ? 'Sí' : 'No'));

    // Actualizar el evento
    $resultado = $eventoController->actualizarEvento($datos, $imagen);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Evento actualizado exitosamente'
        ]);
    } else {
        // Si el resultado no es true, es un mensaje de error
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    error_log("Error en actualizar.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>