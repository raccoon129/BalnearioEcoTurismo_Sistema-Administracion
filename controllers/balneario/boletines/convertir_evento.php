<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinController.php';

header('Content-Type: application/json');
session_start();

try {
    // Debug
    error_log("POST data: " . print_r($_POST, true));
    error_log("SESSION data: " . print_r($_SESSION, true));

    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    // Validar datos recibidos
    if (!isset($_POST['titulo_boletin']) || !isset($_POST['contenido_boletin'])) {
        throw new Exception('Datos incompletos para crear el boletín');
    }

    // Usar el ID de usuario de la sesión
    $id_usuario = $_SESSION['usuario_id'];
    if (!$id_usuario) {
        throw new Exception('Usuario no identificado');
    }

    $boletinController = new BoletinController($db);
    
    // Crear el boletín como borrador
    $resultado = $boletinController->crearBoletin(
        $_POST['titulo_boletin'],
        $_POST['contenido_boletin'],
        $id_usuario,
        true // es_borrador = true
    );

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Boletín creado exitosamente'
        ]);
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    error_log('Error en convertir_evento.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 