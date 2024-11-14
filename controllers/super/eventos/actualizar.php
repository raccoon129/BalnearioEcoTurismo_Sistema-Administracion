<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './EventoSuperController.php';

header('Content-Type: application/json');
session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['superadministrador']);

    // Validar ID de evento
    if (!isset($_POST['id_evento'])) {
        throw new Exception('ID de evento no proporcionado');
    }

    // Validar campos requeridos
    $camposRequeridos = [
        'titulo_evento' => 'Título',
        'descripcion_evento' => 'Descripción',
        'fecha_inicio_evento' => 'Fecha de inicio',
        'fecha_fin_evento' => 'Fecha de fin',
        'id_balneario' => 'Balneario'
    ];

    foreach ($camposRequeridos as $campo => $nombre) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            throw new Exception("El campo {$nombre} es requerido");
        }
    }

    // Validar fechas
    $fechaInicio = strtotime($_POST['fecha_inicio_evento']);
    $fechaFin = strtotime($_POST['fecha_fin_evento']);

    if ($fechaInicio === false || $fechaFin === false) {
        throw new Exception('Formato de fecha inválido');
    }

    if ($fechaFin <= $fechaInicio) {
        throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
    }

    // Preparar datos
    $datos = [
        'titulo_evento' => trim($_POST['titulo_evento']),
        'descripcion_evento' => trim($_POST['descripcion_evento']),
        'fecha_inicio_evento' => $_POST['fecha_inicio_evento'],
        'fecha_fin_evento' => $_POST['fecha_fin_evento'],
        'id_balneario' => $_POST['id_balneario']
    ];

    $eventoController = new EventoSuperController($db);

    // Procesar nueva imagen si se proporcionó
    $urlImagen = null;
    if (isset($_FILES['imagen_evento']) && $_FILES['imagen_evento']['error'] === UPLOAD_ERR_OK) {
        $urlImagen = $eventoController->procesarImagen($_FILES['imagen_evento'], $_POST['id_evento']);
        if ($urlImagen === false) {
            throw new Exception('Error al procesar la imagen');
        }
    }

    // Actualizar evento
    $resultado = $eventoController->actualizarEvento($_POST['id_evento'], $datos, $urlImagen);

    if ($resultado === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Evento actualizado exitosamente'
        ]);
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    error_log('Error en actualizar.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 