<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinController.php';

session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    // Verificar que se recibió el ID del boletín
    if (!isset($_POST['id_boletin'])) {
        throw new Exception('ID de boletín no proporcionado');
    }

    $id_boletin = filter_var($_POST['id_boletin'], FILTER_VALIDATE_INT);
    if (!$id_boletin) {
        throw new Exception('ID de boletín inválido');
    }

    $boletinController = new BoletinController($db);
    
    // Verificar que el boletín existe y pertenece al balneario
    $boletin = $boletinController->obtenerBoletin($id_boletin, $_SESSION['id_balneario']);
    if (!$boletin) {
        throw new Exception('Boletín no encontrado o sin permisos para eliminarlo');
    }

    // Eliminar el boletín
    $resultado = $boletinController->eliminarBoletin($id_boletin);

    if ($resultado === true) {
        header('Location: ../../../views/balneario/boletines/lista.php?success=' . urlencode('Boletín eliminado exitosamente'));
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    header('Location: ../../../views/balneario/boletines/lista.php?error=' . urlencode($e->getMessage()));
}
exit();
?> 