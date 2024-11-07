<?php
// Configuración de autenticación y control de acceso
class Auth {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    // Verificar si el usuario está autenticado
    public function checkAuth() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit();
        }
    }

    // Verificar el rol del usuario
    public function checkRole($allowed_roles) {
        if (!in_array($_SESSION['rol_usuario'], $allowed_roles)) {
            header('Location: acceso_denegado.php');
            exit();
        }
    }
}
?>