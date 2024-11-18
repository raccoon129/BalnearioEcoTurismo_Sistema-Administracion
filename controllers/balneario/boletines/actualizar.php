<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinController.php';

header('Content-Type: application/json');
session_start();

try {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if (!isset($_POST['id_boletin']) || !isset($_POST['titulo']) || !isset($_POST['contenido'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $id_boletin = (int)$_POST['id_boletin'];
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);

    $boletinController = new BoletinController($db);
    $resultado = $boletinController->actualizarBoletin($id_boletin, $titulo, $contenido, $_SESSION['id_balneario']);

    echo json_encode($resultado);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 