<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './ContactoController.php';

header('Content-Type: application/json');
session_start();

try {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    if (!isset($_POST['id_contacto'])) {
        throw new Exception('ID de contacto no proporcionado');
    }

    $id_contacto = (int)$_POST['id_contacto'];
    $contactoController = new ContactoController($db);
    $resultado = $contactoController->eliminarContacto($id_contacto);

    echo json_encode($resultado);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 