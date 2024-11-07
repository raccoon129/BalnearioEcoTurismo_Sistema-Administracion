<?php
session_start();
require_once '../../config/database.php';

// Verificar si se recibieron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        header('Location: ../../login.php?error=' . urlencode('Todos los campos son obligatorios'));
        exit();
    }

    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();

    // Preparar la consulta
    $query = "SELECT id_usuario, nombre_usuario, email_usuario, password_usuario, rol_usuario, id_balneario 
              FROM usuarios 
              WHERE email_usuario = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            // Verificar la contraseña
            if (password_verify($password, $usuario['password_usuario'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                $_SESSION['email_usuario'] = $usuario['email_usuario'];
                $_SESSION['rol_usuario'] = $usuario['rol_usuario'];
                $_SESSION['id_balneario'] = $usuario['id_balneario'];

                // Establecer cookie si se marcó "recordar sesión"
                if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 días
                    
                    // Guardar token en la base de datos (implementar tabla de tokens)
                    // TODO: Implementar sistema de remember me
                }

                // Redirigir según el rol
                header('Location: ../../index.php');
                exit();
            } else {
                header('Location: ../../login.php?error=' . urlencode('Credenciales incorrectas'));
                exit();
            }
        } else {
            header('Location: ../../login.php?error=' . urlencode('Usuario no encontrado'));
            exit();
        }

        $stmt->close();
    } else {
        header('Location: ../../login.php?error=' . urlencode('Error en el servidor'));
        exit();
    }

    $conn->close();
} else {
    header('Location: ../../login.php');
    exit();
}
?>