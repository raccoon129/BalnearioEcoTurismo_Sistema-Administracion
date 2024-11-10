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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventoController = new EventoController($db);
    
    // Procesar la imagen si se subió una
    $url_imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = $_FILES['imagen'];
        $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
        
        // Validar extensión
        $extensiones_permitidas = ['jpg', 'jpeg', 'png'];
        if (!in_array($extension, $extensiones_permitidas)) {
            header('Location: ../../../views/balneario/eventos/lista.php?error=' . urlencode('Formato de imagen no permitido'));
            exit();
        }

        // Validar tamaño (2MB máximo)
        if ($imagen['size'] > 2 * 1024 * 1024) {
            header('Location: ../../../views/balneario/eventos/lista.php?error=' . urlencode('La imagen no debe superar 2MB'));
            exit();
        }

        // Generar nombre único y mover archivo
        $nombre_archivo = uniqid() . '.' . $extension;
        $directorio_destino = '../../../uploads/eventos/';
        
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        if (move_uploaded_file($imagen['tmp_name'], $directorio_destino . $nombre_archivo)) {
            $url_imagen = '../../../uploads/eventos/' . $nombre_archivo;
        } else {
            header('Location: ../../../views/balneario/eventos/lista.php?error=' . urlencode('Error al subir la imagen'));
            exit();
        }
    }

    // Preparar datos del evento
    $datos = [
        'id_balneario' => $_SESSION['id_balneario'],
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin'],
        'url_imagen' => $url_imagen
    ];

    // Validar fechas
    if (strtotime($datos['fecha_fin']) < strtotime($datos['fecha_inicio'])) {
        header('Location: ../../../views/balneario/eventos/lista.php?error=' . urlencode('La fecha de fin no puede ser anterior a la fecha de inicio'));
        exit();
    }

    // Intentar crear el evento
    $resultado = $eventoController->crearEvento($datos);

    if ($resultado === true) {
        header('Location: ../../../views/balneario/eventos/lista.php?success=' . urlencode('¡El evento ha sido creado exitosamente!'));
    } else {
        header('Location: ../../../views/balneario/eventos/lista.php?error=' . urlencode($resultado));
    }
    exit();
}

header('Location: ../../../views/balneario/eventos/lista.php');
exit();
?>