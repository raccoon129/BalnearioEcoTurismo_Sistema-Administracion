<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Log inicial
    error_log("Iniciando actualización de boletín");
    error_log("POST data: " . print_r($_POST, true));

    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Validar campos requeridos
    $requiredFields = ['id_boletin', 'titulo_boletin', 'contenido_boletin', 'id_balneario'];
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        throw new Exception('Campos requeridos faltantes: ' . implode(', ', $missingFields));
    }

    $id_boletin = (int)$_POST['id_boletin'];
    $id_balneario = (int)$_POST['id_balneario'];
    $titulo = trim($_POST['titulo_boletin']);
    $contenido = trim($_POST['contenido_boletin']);
    $id_usuario = $auth->getUsuarioId();

    error_log("Datos procesados:");
    error_log("ID Boletín: $id_boletin");
    error_log("ID Balneario: $id_balneario");
    error_log("ID Usuario: $id_usuario");

    // Validar que el boletín exista y sea un borrador
    $boletinController = new BoletinSuperController($db, $id_usuario);
    $boletin = $boletinController->obtenerDetallesBoletinBalneario($id_boletin);

    if (!$boletin) {
        throw new Exception('Boletín no encontrado');
    }

    error_log("Estado del boletín: " . $boletin['estado_boletin']);

    if ($boletin['estado_boletin'] !== 'borrador') {
        throw new Exception('Solo se pueden editar boletines en estado borrador');
    }

    // Preparar la consulta de actualización
    $query = "UPDATE boletines b
             INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
             SET b.titulo_boletin = ?,
                 b.contenido_boletin = ?
             WHERE b.id_boletin = ? 
             AND u.id_balneario = ?
             AND b.fecha_envio_boletin IS NULL";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $db->error);
    }

    $stmt->bind_param("ssii", $titulo, $contenido, $id_boletin, $id_balneario);

    error_log("Ejecutando consulta de actualización");
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }

    error_log("Filas afectadas: " . $stmt->affected_rows);
    if ($stmt->affected_rows === 0) {
        throw new Exception("No se pudo actualizar el boletín. Verifique que sea un borrador y tenga los permisos necesarios");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Boletín guardado exitosamente como borrador',
        'data' => [
            'id_boletin' => $id_boletin,
            'id_balneario' => $id_balneario,
            'titulo' => $titulo
        ]
    ]);

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
            'trace' => $e->getTraceAsString(),
            'post_data' => $_POST
        ]
    ]);
}
?> 