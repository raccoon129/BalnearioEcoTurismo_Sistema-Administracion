<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './EventoSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    if (!isset($_POST['id_evento'])) {
        throw new Exception('ID de evento no proporcionado');
    }

    $eventoController = new EventoSuperController($db);
    
    // Verificar si el evento existe y puede ser eliminado
    if (!$eventoController->puedeEditarEvento($_POST['id_evento'])) {
        throw new Exception('No se puede eliminar un evento que ya ha finalizado');
    }

    $resultado = $eventoController->eliminarEvento($_POST['id_evento']);

    // Si el resultado es una URL de imagen, eliminarla del servidor
    if (is_string($resultado) && strpos($resultado, 'assets/img/eventos/') === 0) {
        $rutaImagen = '../../../' . $resultado;
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Evento eliminado exitosamente'
    ]);

} catch (Exception $e) {
    error_log('Error en eliminar.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 