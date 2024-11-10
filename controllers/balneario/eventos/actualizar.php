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
        header('Location: ../../views/balneario/eventos/lista.php?error=' . urlencode('No tiene permiso para editar este evento'));
        exit();
    }

    // Obtener evento actual para la imagen
    $evento_actual = $eventoController->obtenerEvento($_POST['id_evento']);
    $url_imagen = $evento_actual['url_imagen_evento'];

    // Procesar nueva imagen si se subió una
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = $_FILES['imagen'];
        $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
        
        // Validar extensión
        $extensiones_permitidas = ['jpg', 'jpeg', 'png'];
        if (!in_array($extension, $extensiones_permitidas)) {
            header('Location: ../../views/balneario/eventos/editar.php?id=' . $_POST['id_evento'] . '&error=' . urlencode('Formato de imagen no permitido'));
            exit();
        }

        // Validar tamaño (2MB máximo)
        if ($imagen['size'] > 2 * 1024 * 1024) {
            header('Location: ../../views/balneario/eventos/editar.php?id=' . $_POST['id_evento'] . '&error=' . urlencode('La imagen no debe superar 2MB'));
            exit();
        }

        // Eliminar imagen anterior si existe
        if ($url_imagen && file_exists('../../' . $url_imagen)) {
            unlink('../../' . $url_imagen);
        }

        // Generar nombre único y mover archivo
        $nombre_archivo = uniqid() . '.' . $extension;
        $directorio_destino = '../../uploads/eventos/';
        
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        if (move_uploaded_file($imagen['tmp_name'], $directorio_destino . $nombre_archivo)) {
            $url_imagen = 'uploads/eventos/' . $nombre_archivo;
        } else {
            header('Location: ../../views/eventos/editar.php?id=' . $_POST['id_evento'] . '&error=' . urlencode('Error al subir la imagen'));
            exit();
        }
    }

    // Preparar datos del evento
    $datos = [
        'id_evento' => $_POST['id_evento'],
        'id_balneario' => $_SESSION['id_balneario'],
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin'],
        'url_imagen' => $url_imagen
    ];

    // Validar fechas
    if (strtotime($datos['fecha_fin']) < strtotime($datos['fecha_inicio'])) {
        header('Location: ../../views/balneario/eventos/editar.php?id=' . $_POST['id_evento'] . '&error=' . urlencode('La fecha de fin no puede ser anterior a la fecha de inicio'));
        exit();
    }

    // Intentar actualizar el evento
    $resultado = $eventoController->actualizarEvento($datos);

    if ($resultado === true) {
        header('Location: ../../views/balneario/eventos/lista.php?success=' . urlencode('Evento actualizado exitosamente'));
    } else {
        header('Location: ../../views/balneario/eventos/editar.php?id=' . $_POST['id_evento'] . '&error=' . urlencode($resultado));
    }
    exit();
}

header('Location: ../../views/balneario/eventos/lista.php');
exit();
?>