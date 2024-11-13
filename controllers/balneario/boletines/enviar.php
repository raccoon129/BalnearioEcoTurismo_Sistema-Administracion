<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once './BoletinController.php';
require_once '../../../vendor/autoload.php'; // Para PHPMailer

session_start();

try {
    // Verificar autenticación
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);

    $auth->checkAuth();
    $auth->checkRole(['administrador_balneario']);

    if (!isset($_POST['id_boletin'])) {
        throw new Exception('ID de boletín no proporcionado');
    }

    $id_boletin = filter_var($_POST['id_boletin'], FILTER_VALIDATE_INT);
    if (!$id_boletin) {
        throw new Exception('ID de boletín inválido');
    }

    $boletinController = new BoletinController($db);
    $resultado = $boletinController->enviarBoletin($id_boletin, $_SESSION['id_balneario']);

    if ($resultado['success']) {
        $mensaje = sprintf(
            'Boletín enviado exitosamente a %d de %d suscriptores', 
            $resultado['enviados'], 
            $resultado['total_suscriptores']
        );
        header('Location: ../../../views/balneario/boletines/lista.php?success=' . urlencode($mensaje));
    } else {
        throw new Exception('Error al enviar el boletín: ' . $resultado['message']);
    }

} catch (Exception $e) {
    header('Location: ../../../views/balneario/boletines/lista.php?error=' . urlencode($e->getMessage()));
}
exit();
?> 