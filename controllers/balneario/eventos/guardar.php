<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './EventoController.php';

session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $eventoController = new EventoController($db);
        
        // Preparar datos del evento
        $datos = [
            'id_balneario' => $_SESSION['id_balneario'],
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'],
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_fin' => $_POST['fecha_fin']
        ];

        // Validar fechas
        if (strtotime($datos['fecha_fin']) < strtotime($datos['fecha_inicio'])) {
            throw new Exception('La fecha de fin no puede ser anterior a la fecha de inicio');
        }

        // Procesar imagen si se proporcionó una
        $imagen = isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK ? $_FILES['imagen'] : null;

        // Crear el evento
        $resultado = $eventoController->crearEvento($datos, $imagen);

        if ($resultado === true) {
            echo json_encode([
                'success' => true,
                'message' => '¡El evento ha sido creado exitosamente!'
            ]);
        } else {
            throw new Exception($resultado);
        }
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>