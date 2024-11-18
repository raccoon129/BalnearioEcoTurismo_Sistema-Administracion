<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinController.php';

header('Content-Type: application/json');
session_start();

try {
    // Debug
    error_log("Iniciando guardado de boletín");
    error_log("POST data: " . print_r($_POST, true));
    error_log("SESSION data: " . print_r($_SESSION, true));

    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    // Validar campos requeridos
    if (!isset($_POST['titulo']) || !isset($_POST['contenido'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('Usuario no identificado');
    }
    $id_usuario = $_SESSION['usuario_id'];

    error_log("ID de usuario obtenido de sesión: " . $id_usuario);

    $boletinController = new BoletinController($db);
    $resultado = $boletinController->crearBorrador($titulo, $contenido, $id_usuario);

    if ($resultado['success']) {
        echo json_encode($resultado);
    } else {
        throw new Exception($resultado['message']);
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