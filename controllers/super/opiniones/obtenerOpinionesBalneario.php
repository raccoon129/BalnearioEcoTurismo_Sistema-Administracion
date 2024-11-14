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

    // Validar ID del balneario
    if (!isset($_GET['id_balneario'])) {
        throw new Exception('ID de balneario no proporcionado');
    }

    $id_balneario = (int)$_GET['id_balneario'];

    // Obtener estadísticas específicas del balneario
    $opinionController = new OpinionSuperController($db);
    $estadisticas = $opinionController->obtenerEstadisticas($id_balneario);

    // Obtener opiniones del balneario
    $filtros = [
        'id_balneario' => $id_balneario,
        'estado_validacion' => $_GET['estado'] ?? null,
        'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
        'fecha_fin' => $_GET['fecha_fin'] ?? null
    ];

    $opiniones = $opinionController->obtenerOpiniones($filtros);

    echo json_encode([
        'success' => true,
        'estadisticas' => $estadisticas,
        'opiniones' => $opiniones
    ]);

} catch (Exception $e) {
    error_log('Error en obtenerOpinionesBalneario.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 