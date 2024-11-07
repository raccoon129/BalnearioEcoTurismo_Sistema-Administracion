<?php
session_start();
require_once '../../config/database.php';

// Verificar si ya existen usuarios
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT COUNT(*) as total FROM usuarios";
$result = $conn->query($query);
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
        header('Location: ../../setup.php?error=' . urlencode('Todos los campos son obligatorios'));
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../../setup.php?error=' . urlencode('El correo electrónico no es válido'));
        exit();
    }

    if ($password !== $confirm_password) {
        header('Location: ../../setup.php?error=' . urlencode('Las contraseñas no coinciden'));
        exit();
    }

    if (strlen($password) < 8) {
        header('Location: ../../setup.php?error=' . urlencode('La contraseña debe tener al menos 8 caracteres'));
        exit();
    }

    // Cifrar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar superadministrador
    $query = "INSERT INTO usuarios (nombre_usuario, email_usuario, password_usuario, rol_usuario) 
              VALUES (?, ?, ?, 'superadministrador')";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sss", $nombre, $email, $password_hash);
        
        if ($stmt->execute()) {
            // Redirigir al login con mensaje de éxito
            header('Location: ../../login.php?success=' . urlencode('Cuenta de superadministrador creada exitosamente'));
            exit();
        } else {
            header('Location: ../../setup.php?error=' . urlencode('Error al crear la cuenta'));
            exit();
        }

        $stmt->close();
    } else {
        header('Location: ../../setup.php?error=' . urlencode('Error en el servidor'));
        exit();
    }

    $conn->close();
} else {
    header('Location: ../../setup.php');
    exit();
}
?>