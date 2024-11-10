<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once '../../../controllers/balneario/eventos/EventoController.php';

session_start();

// Verificar autenticación
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$auth->checkAuth();
$auth->checkRole(['administrador_balneario']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_evento'])) {
    $eventoController = new EventoController($db);
    
    // Verificar que el evento pertenece al balneario del usuario
    if (!$eventoController->verificarPertenencia($_POST['id_evento'], $_SESSION['id_balneario'])) {
        header('Location: ../../views/balneario/eventos/lista.php?error=' . urlencode('No tiene permiso para eliminar este evento'));
        exit();
    }

    // Obtener información del evento para eliminar la imagen
    $evento = $eventoController->obtenerEvento($_POST['id_evento']);
    if ($evento && $evento['url_imagen_evento']) {
        $ruta_imagen = '../../' . $evento['url_imagen_evento'];
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }

    // Intentar eliminar el evento
    $resultado = $eventoController->eliminarEvento($_POST['id_evento'], $_SESSION['id_balneario']);

    if ($resultado === true) {
        header('Location: ../../views/balneario/eventos/lista.php?success=' . urlencode('Evento eliminado exitosamente'));
    } else {
        header('Location: ../../views/balneario/eventos/lista.php?error=' . urlencode($resultado));
    }
    exit();
}

header('Location: ../../views/balneario/eventos/lista.php');
exit();
?>