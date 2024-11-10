<?php
/**
 * Controlador para la gestión de promociones
 * Maneja todas las operaciones CRUD relacionadas con promociones de los balnearios
 */
class PromocionController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todas las promociones de un balneario específico
     * @param int $id_balneario ID del balneario
     * @return array Lista de promociones con todos sus detalles
     */
    public function obtenerPromociones($id_balneario) {
        $query = "SELECT * FROM promociones 
                 WHERE id_balneario = ? 
                 ORDER BY fecha_inicio_promocion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $promociones = [];
        while ($row = $result->fetch_assoc()) {
            $promociones[] = $row;
        }
        
        return $promociones;
    }

    /**
     * Obtiene los detalles de una promoción específica
     * @param int $id_promocion ID de la promoción
     * @return array|null Detalles de la promoción o null si no existe
     */
    public function obtenerPromocion($id_promocion) {
        $query = "SELECT p.*, b.nombre_balneario 
                 FROM promociones p 
                 INNER JOIN balnearios b ON p.id_balneario = b.id_balneario 
                 WHERE p.id_promocion = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_promocion);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Crea una nueva promoción
     * @param array $datos Datos de la promoción
     * @return bool|string True si se creó correctamente, mensaje de error si falló
     */
    public function crearPromocion($datos) {
        $query = "INSERT INTO promociones (
                    id_balneario, titulo_promocion, descripcion_promocion,
                    fecha_inicio_promocion, fecha_fin_promocion
                ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "issss",
            $datos['id_balneario'],
            $datos['titulo'],
            $datos['descripcion'],
            $datos['fecha_inicio'],
            $datos['fecha_fin']
        );
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al crear la promoción: " . $stmt->error;
    }

    /**
     * Actualiza una promoción existente
     * @param array $datos Datos actualizados de la promoción
     * @return bool|string True si se actualizó correctamente, mensaje de error si falló
     */
    public function actualizarPromocion($datos) {
        $query = "UPDATE promociones SET 
                    titulo_promocion = ?,
                    descripcion_promocion = ?,
                    fecha_inicio_promocion = ?,
                    fecha_fin_promocion = ?
                 WHERE id_promocion = ? AND id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "ssssii",
            $datos['titulo'],
            $datos['descripcion'],
            $datos['fecha_inicio'],
            $datos['fecha_fin'],
            $datos['id_promocion'],
            $datos['id_balneario']
        );
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al actualizar la promoción: " . $stmt->error;
    }

    /**
     * Elimina una promoción
     * @param int $id_promocion ID de la promoción
     * @param int $id_balneario ID del balneario (para verificación)
     * @return bool|string True si se eliminó correctamente, mensaje de error si falló
     */
    public function eliminarPromocion($id_promocion, $id_balneario) {
        $query = "DELETE FROM promociones 
                 WHERE id_promocion = ? AND id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_promocion, $id_balneario);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al eliminar la promoción: " . $stmt->error;
    }

    /**
     * Verifica si una promoción pertenece a un balneario específico
     * @param int $id_promocion ID de la promoción
     * @param int $id_balneario ID del balneario
     * @return bool True si la promoción pertenece al balneario, False si no
     */
    public function verificarPertenencia($id_promocion, $id_balneario) {
        $query = "SELECT id_promocion FROM promociones 
                 WHERE id_promocion = ? AND id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_promocion, $id_balneario);
        $stmt->execute();
        
        return $stmt->get_result()->num_rows > 0;
    }
}
?>