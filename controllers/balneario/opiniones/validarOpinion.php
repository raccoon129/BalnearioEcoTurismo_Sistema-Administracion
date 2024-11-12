<?php
define('BASE_PATH', dirname(dirname(dirname(__DIR__))));

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/auth.php';
require_once __DIR__ . '/OpinionController.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_opinion'], $_POST['validada'])) {
        throw new Exception('Solicitud inválida');
    }

    $opinionController = new OpinionController($db);
    
    // Verificar que la opinión pertenece al balneario del usuario
    $opinion = $opinionController->obtenerOpinion($_POST['id_opinion']);
    if (!$opinion || $opinion['id_balneario'] != $_SESSION['id_balneario']) {
        throw new Exception('No tiene permiso para modificar esta opinión');
    }

    // Realizar la actualización
    $validada = $_POST['validada'] === '1';
    $resultado = $opinionController->actualizarValidacion($_POST['id_opinion'], $validada);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => $validada ? 'Opinión validada exitosamente' : 'Opinión invalidada exitosamente'
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
