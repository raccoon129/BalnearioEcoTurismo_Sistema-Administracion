<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Validar campos requeridos
    if (!isset($_POST['id_boletin']) || empty($_POST['titulo_boletin']) || empty($_POST['contenido_boletin'])) {
        throw new Exception('Todos los campos son requeridos');
    }

    // Preparar la consulta
    $query = "UPDATE boletines 
             SET titulo_boletin = ?,
                 contenido_boletin = ?
             WHERE id_boletin = ? 
             AND id_usuario = ?";

    $stmt = $database->getConnection()->prepare($query);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta");
    }

    $titulo = trim($_POST['titulo_boletin']);
    $contenido = trim($_POST['contenido_boletin']);
    $id_boletin = (int)$_POST['id_boletin'];
    $id_usuario = $auth->getUsuarioId();

    $stmt->bind_param("ssii", $titulo, $contenido, $id_boletin, $id_usuario);

    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar el boletín");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("No se encontró el boletín o no tiene permisos para editarlo");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Boletín actualizado exitosamente'
    ]);

} catch (Exception $e) {
    error_log('Error en actualizar.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 