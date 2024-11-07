<?php
session_start();
require_once 'config/database.php';

// Si ya está autenticado, redirigir al dashboard correspondiente
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_usuario'] === 'superadministrador') {
        header('Location: dashboardSuper.php');
    } else {
        header('Location: dashboardBalneario.php');
    }
    exit();
}

// Verificar si existen usuarios en la base de datos
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT COUNT(*) as total FROM usuarios";
$result = $conn->query($query);
$row = $result->fetch_assoc();

if ($row['total'] == 0) {
    // No hay usuarios, redirigir a la página de configuración inicial
    header('Location: setup.php');
} else {
    // Ya existen usuarios, redirigir al login
    header('Location: login.php');
}
exit();
?>