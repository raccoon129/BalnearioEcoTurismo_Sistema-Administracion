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

    // Verificar que se recibieron los datos necesarios
    if (!isset($_POST['titulo']) || !isset($_POST['contenido'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);

    // Validar datos
    if (empty($titulo) || empty($contenido)) {
        throw new Exception('Los campos título y contenido son obligatorios');
    }

    $boletinController = new BoletinController($db);
    
    // Crear el boletín como borrador
    $resultado = $boletinController->crearBoletin(
        $titulo,
        $contenido,
        $_SESSION['id_usuario'],
        true // es_borrador = true
    );

    if ($resultado === true) {
        header('Location: ../../../views/balneario/boletines/lista.php?success=' . urlencode('Boletín guardado como borrador'));
    } else {
        throw new Exception($resultado);
    }

} catch (Exception $e) {
    header('Location: ../../../views/balneario/boletines/lista.php?error=' . urlencode($e->getMessage()));
}
exit();
?> 