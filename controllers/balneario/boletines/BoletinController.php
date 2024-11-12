<?php
class BoletinController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los boletines asociados a un balneario específico
     * @param int $id_balneario ID del balneario
     * @return array Lista de boletines
     */
    public function obtenerBoletines($id_balneario) {
        $query = "SELECT b.*, u.nombre_usuario 
                 FROM boletines b 
                 INNER JOIN usuarios u ON b.id_usuario = u.id_usuario 
                 WHERE u.id_balneario = ?
                 ORDER BY b.fecha_envio_boletin DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crea un nuevo boletín
     * @param string $titulo Título del boletín
     * @param string $contenido Contenido del boletín
     * @param int $id_usuario ID del usuario que crea el boletín
     * @param bool $es_borrador Indica si el boletín es un borrador
     * @return bool|string True si se creó correctamente, mensaje de error en caso contrario
     */
    public function crearBoletin($titulo, $contenido, $id_usuario, $es_borrador = true) {
        try {
            $query = "INSERT INTO boletines (titulo_boletin, contenido_boletin, fecha_envio_boletin, id_usuario) 
                     VALUES (?, ?, ?, ?)";
            
            // Si es borrador, la fecha_envio_boletin será NULL
            $fecha_envio = $es_borrador ? null : date('Y-m-d H:i:s');
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssi", $titulo, $contenido, $fecha_envio, $id_usuario);
            
            if ($stmt->execute()) {
                return true;
            }
            
            throw new Exception("Error al crear el boletín: " . $stmt->error);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Obtiene un boletín específico
     * @param int $id_boletin ID del boletín
     * @param int $id_balneario ID del balneario para verificar permisos
     * @return array|bool Datos del boletín o false si no existe o no tiene permisos
     */
    public function obtenerBoletin($id_boletin, $id_balneario) {
        $query = "SELECT b.*, u.nombre_usuario 
                 FROM boletines b 
                 INNER JOIN usuarios u ON b.id_usuario = u.id_usuario 
                 WHERE b.id_boletin = ? AND u.id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_boletin, $id_balneario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Envía un boletín a los suscriptores
     * @param int $id_boletin ID del boletín a enviar
     * @return bool|string True si se envió correctamente, mensaje de error en caso contrario
     */
    public function enviarBoletin($id_boletin) {
        // Aquí se implementará la lógica de envío usando Mailtrap
        // Por ahora solo actualizamos la fecha de envío
        $fecha_envio = date('Y-m-d H:i:s');
        
        $query = "UPDATE boletines SET fecha_envio_boletin = ? WHERE id_boletin = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $fecha_envio, $id_boletin);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al enviar el boletín: " . $stmt->error;
    }

    /**
     * Elimina un boletín
     * @param int $id_boletin ID del boletín a eliminar
     * @return bool|string True si se eliminó correctamente, mensaje de error en caso contrario
     */
    public function eliminarBoletin($id_boletin) {
        $query = "DELETE FROM boletines WHERE id_boletin = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_boletin);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al eliminar el boletín: " . $stmt->error;
    }
}
?> 