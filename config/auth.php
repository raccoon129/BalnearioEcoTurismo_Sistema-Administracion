<?php
// Configuración de autenticación y control de acceso
class Auth {
    private $conn;
    private $usuario_id;
    
    public function __construct($db) {
        $this->conn = $db;
        // Inicializar el ID del usuario si hay una sesión activa
        if (isset($_SESSION['usuario_id'])) {
            $this->usuario_id = $_SESSION['usuario_id'];
        }
    }

    // Verificar si el usuario está autenticado
    public function checkAuth() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit();
        }
        // Almacenar el ID del usuario cuando se verifica la autenticación
        $this->usuario_id = $_SESSION['usuario_id'];
    }

    // Verificar el rol del usuario
    public function checkRole($allowed_roles) {
        if (!in_array($_SESSION['rol_usuario'], $allowed_roles)) {
            header('Location: acceso_denegado.php');
            exit();
        }
    }

    // Obtener el ID del usuario autenticado
    public function getUsuarioId() {
        return $this->usuario_id;
    }
}
?>