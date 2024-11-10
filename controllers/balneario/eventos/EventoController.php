<?php
/**
 * Controlador para la gestión de eventos
 * Maneja todas las operaciones CRUD relacionadas con eventos de los balnearios
 */
class EventoController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los eventos de un balneario específico
     * @param int $id_balneario ID del balneario
     * @return array Lista de eventos con todos sus detalles
     */
    public function obtenerEventos($id_balneario) {
        $query = "SELECT * FROM eventos 
                 WHERE id_balneario = ? 
                 ORDER BY fecha_inicio_evento DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $eventos = [];
        while ($row = $result->fetch_assoc()) {
            $eventos[] = $row;
        }
        
        return $eventos;
    }

    /**
     * Obtiene los detalles de un evento específico
     * @param int $id_evento ID del evento
     * @return array|null Detalles del evento o null si no existe
     */
    public function obtenerEvento($id_evento) {
        $query = "SELECT e.*, b.nombre_balneario 
                 FROM eventos e 
                 INNER JOIN balnearios b ON e.id_balneario = b.id_balneario 
                 WHERE e.id_evento = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_evento);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Crea un nuevo evento
     * @param array $datos Datos del evento
     * @return bool|string True si se creó correctamente, mensaje de error si falló
     */
    public function crearEvento($datos) {
        $query = "INSERT INTO eventos (
                    id_balneario, titulo_evento, descripcion_evento,
                    url_imagen_evento, fecha_inicio_evento, fecha_fin_evento
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "isssss",
            $datos['id_balneario'],
            $datos['titulo'],
            $datos['descripcion'],
            $datos['url_imagen'],
            $datos['fecha_inicio'],
            $datos['fecha_fin']
        );
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al crear el evento: " . $stmt->error;
    }

    /**
     * Actualiza un evento existente
     * @param array $datos Datos actualizados del evento
     * @return bool|string True si se actualizó correctamente, mensaje de error si falló
     */
    public function actualizarEvento($datos) {
        $query = "UPDATE eventos SET 
                    titulo_evento = ?,
                    descripcion_evento = ?,
                    url_imagen_evento = ?,
                    fecha_inicio_evento = ?,
                    fecha_fin_evento = ?
                 WHERE id_evento = ? AND id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sssssii",
            $datos['titulo'],
            $datos['descripcion'],
            $datos['url_imagen'],
            $datos['fecha_inicio'],
            $datos['fecha_fin'],
            $datos['id_evento'],
            $datos['id_balneario']
        );
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al actualizar el evento: " . $stmt->error;
    }

    /**
     * Elimina un evento
     * @param int $id_evento ID del evento
     * @param int $id_balneario ID del balneario (para verificación)
     * @return bool|string True si se eliminó correctamente, mensaje de error si falló
     */
    public function eliminarEvento($id_evento, $id_balneario) {
        $query = "DELETE FROM eventos 
                 WHERE id_evento = ? AND id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_evento, $id_balneario);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al eliminar el evento: " . $stmt->error;
    }

    /**
     * Verifica si un evento pertenece a un balneario específico
     * @param int $id_evento ID del evento
     * @param int $id_balneario ID del balneario
     * @return bool True si el evento pertenece al balneario, False si no
     */
    public function verificarPertenencia($id_evento, $id_balneario) {
        $query = "SELECT id_evento FROM eventos 
                 WHERE id_evento = ? AND id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_evento, $id_balneario);
        $stmt->execute();
        
        return $stmt->get_result()->num_rows > 0;
    }
}
?>