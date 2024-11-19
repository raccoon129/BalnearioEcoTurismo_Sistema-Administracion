<?php
class ContactoController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los contactos
     */
    public function obtenerContactos() {
        try {
            $query = "SELECT * FROM contactos ORDER BY id_contacto DESC";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta");
            }

            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta");
            }

            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en obtenerContactos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un contacto específico
     */
    public function obtenerContacto($id_contacto) {
        try {
            $query = "SELECT * FROM contactos WHERE id_contacto = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta");
            }

            $stmt->bind_param("i", $id_contacto);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta");
            }

            $result = $stmt->get_result();
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Error en obtenerContacto: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Elimina un contacto
     */
    public function eliminarContacto($id_contacto) {
        try {
            $query = "DELETE FROM contactos WHERE id_contacto = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta");
            }

            $stmt->bind_param("i", $id_contacto);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar el contacto");
            }

            return [
                'success' => true,
                'message' => 'Contacto eliminado exitosamente'
            ];
            
        } catch (Exception $e) {
            error_log("Error en eliminarContacto: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?> 