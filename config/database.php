<?php
// Configuración de la base de datos usando MySQLi
class Database {
    private $host = "localhost";
    private $db_name = "balnearioecoturismo";
    private $username = "root";
    private $password = "";
    public $conn;

    // Obtener la conexión a la base de datos
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            // Verificar la conexión
            if ($this->conn->connect_error) {
                throw new Exception("Error de conexión: " . $this->conn->connect_error);
            }

            // Establecer el conjunto de caracteres
            $this->conn->set_charset("utf8");
            
        } catch(Exception $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>