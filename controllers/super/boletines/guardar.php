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
    if (empty($_POST['titulo_boletin']) || empty($_POST['contenido_boletin'])) {
        throw new Exception('Todos los campos son requeridos');
    }

    // Preparar la consulta
    $query = "INSERT INTO boletines (
                titulo_boletin, 
                contenido_boletin,
                id_usuario,
                fecha_envio_boletin
            ) VALUES (?, ?, ?, NULL)";

    $stmt = $database->getConnection()->prepare($query);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta");
    }

    $titulo = trim($_POST['titulo_boletin']);
    $contenido = trim($_POST['contenido_boletin']);
    $id_usuario = $auth->getUsuarioId();

    $stmt->bind_param("ssi", $titulo, $contenido, $id_usuario);

    if (!$stmt->execute()) {
        throw new Exception("Error al guardar el boletín");
    }

    $id_boletin = $stmt->insert_id;

    echo json_encode([
        'success' => true,
        'message' => 'Boletín guardado como borrador exitosamente',
        'id_boletin' => $id_boletin
    ]);

} catch (Exception $e) {
    error_log('Error en guardar.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 