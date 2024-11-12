<?php
/**
 * Controlador para la gestión de opiniones de usuarios
 * Maneja todas las operaciones relacionadas con opiniones y valoraciones
 */
class OpinionController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todas las opiniones de un balneario específico
     * @param int $id_balneario ID del balneario
     * @return array Lista de opiniones con todos sus detalles
     */
    public function obtenerOpiniones($id_balneario) {
        $query = "SELECT * FROM opiniones_usuarios 
                 WHERE id_balneario = ? 
                 ORDER BY fecha_registro_opinion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $opiniones = [];
        while ($row = $result->fetch_assoc()) {
            $opiniones[] = $row;
        }
        
        return $opiniones;
    }

    /**
     * Obtiene los detalles de una opinión específica
     * @param int $id_opinion ID de la opinión
     * @return array|null Detalles de la opinión o null si no existe
     */
    public function obtenerOpinion($id_opinion) {
        $query = "SELECT o.*, b.nombre_balneario 
                 FROM opiniones_usuarios o 
                 INNER JOIN balnearios b ON o.id_balneario = b.id_balneario 
                 WHERE o.id_opinion = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_opinion);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Actualiza el estado de validación de una opinión
     * @param int $id_opinion ID de la opinión
     * @param bool $validada Nuevo estado de validación
     * @return bool|string True si se actualizó correctamente, mensaje de error si falló
     */
    public function actualizarValidacion($id_opinion, $validada) {
        try {
            $query = "UPDATE opiniones_usuarios SET opinion_validada = ? WHERE id_opinion = ?";
            $stmt = $this->conn->prepare($query);
            
            // Convertir a entero (1 o 0)
            $estado = $validada ? 1 : 0;
            
            $stmt->bind_param("ii", $estado, $id_opinion);
            
            if ($stmt->execute()) {
                return true;
            }
            
            return "Error al actualizar el estado de la opinión";
        } catch (Exception $e) {
            return "Error en la base de datos: " . $e->getMessage();
        }
    }

    /**
     * Elimina una opinión
     * @param int $id_opinion ID de la opinión
     * @param int $id_balneario ID del balneario (para verificación)
     * @return bool|string True si se eliminó correctamente, mensaje de error si falló
     */
    /*
    public function eliminarOpinion($id_opinion, $id_balneario) {
        // Primero verificar que la opinión pertenece al balneario
        if (!$this->verificarPertenencia($id_opinion, $id_balneario)) {
            return "La opinión no pertenece a este balneario";
        }

        // Obtener información de la opinión para eliminar la foto si existe
        $opinion = $this->obtenerOpinion($id_opinion);
        if ($opinion && $opinion['url_foto_opinion']) {
            $ruta_foto = '../../' . $opinion['url_foto_opinion'];
            if (file_exists($ruta_foto)) {
                unlink($ruta_foto);
            }
        }

        $query = "DELETE FROM opiniones_usuarios 
                 WHERE id_opinion = ? AND id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_opinion, $id_balneario);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al eliminar la opinión: " . $stmt->error;
    }
*/
    /**
     * Verifica si una opinión pertenece a un balneario específico
     * @param int $id_opinion ID de la opinión
     * @param int $id_balneario ID del balneario
     * @return bool True si la opinión pertenece al balneario, False si no
     */
    private function verificarPertenencia($id_opinion, $id_balneario) {
        $query = "SELECT id_opinion FROM opiniones_usuarios 
                 WHERE id_opinion = ? AND id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_opinion, $id_balneario);
        $stmt->execute();
        
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Obtiene las opiniones validadas de un balneario
     */
    public function obtenerOpinionesValidadas($id_balneario) {
        $query = "SELECT * FROM opiniones_usuarios 
                 WHERE id_balneario = ? AND opinion_validada = 1
                 ORDER BY fecha_registro_opinion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene las opiniones pendientes de validar
     */
    public function obtenerOpinionesPendientes($id_balneario) {
        $query = "SELECT * FROM opiniones_usuarios 
                 WHERE id_balneario = ? AND opinion_validada IS NULL
                 ORDER BY fecha_registro_opinion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene las opiniones invalidadas
     */
    public function obtenerOpinionesInvalidadas($id_balneario) {
        $query = "SELECT * FROM opiniones_usuarios 
                 WHERE id_balneario = ? AND opinion_validada = 0
                 ORDER BY fecha_registro_opinion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>