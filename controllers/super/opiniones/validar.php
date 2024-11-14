<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './OpinionSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Validar parámetros recibidos
    if (!isset($_POST['id_opinion'])) {
        throw new Exception('ID de opinión no proporcionado');
    }

    if (!isset($_POST['validar'])) {
        throw new Exception('Acción de validación no especificada');
    }

    $id_opinion = (int)$_POST['id_opinion'];
    $validar = filter_var($_POST['validar'], FILTER_VALIDATE_BOOLEAN);

    // Validar la opinión
    $opinionController = new OpinionSuperController($db);
    $resultado = $opinionController->validarOpinion($id_opinion, $validar);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Opinión ' . ($validar ? 'validada' : 'invalidada') . ' exitosamente'
        ]);
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    error_log('Error en validar.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 