<?php
/*
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once 'OpinionController.php';

session_start();

// Verificar autenticación
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$auth->checkAuth();
$auth->checkRole(['administrador_balneario']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_opinion'])) {
    $opinionController = new OpinionController($db);
    
    // Intentar eliminar la opinión
    $resultado = $opinionController->eliminarOpinion($_POST['id_opinion'], $_SESSION['id_balneario']);

    if ($resultado === true) {
        header('Location: ../../../views/balneario/opiniones/lista.php?success=' . urlencode('Opinión eliminada exitosamente'));
    } else {
        header('Location: ../../../views/balneario/opiniones/lista.php?error=' . urlencode($resultado));
    }
    exit();
}

header('Location: ../../../views/balneario/opiniones/lista.php');
exit();
*/
?>