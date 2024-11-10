<?php
/**
 * Controlador para manejar las estadísticas del dashboard de administrador
 * Proporciona métodos para obtener datos resumidos y estadísticas del balneario
 */
class EstadisticasController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene las reservaciones próximas a partir de la fecha actual
     * @param int $id_balneario ID del balneario
     * @param int $limite Cantidad de reservas a obtener (opcional)
     * @return array Lista de reservaciones próximas
     */
    public function obtenerReservasProximas($id_balneario, $limite = 5) {
        $query = "SELECT * FROM reservaciones 
                 WHERE id_balneario = ? 
                 AND fecha_reserva >= CURDATE()
                 ORDER BY fecha_reserva ASC, hora_reserva ASC 
                 LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_balneario, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        
        return $reservas;
    }

    /**
     * Obtiene las opiniones registradas en los últimos 7 días
     * @param int $id_balneario ID del balneario
     * @return array Lista de opiniones recientes
     */
    public function obtenerOpinionesRecientes($id_balneario) {
        $query = "SELECT * FROM opiniones_usuarios 
                 WHERE id_balneario = ? 
                 AND fecha_registro_opinion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
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
     * Obtiene la información básica del balneario
     * @param int $id_balneario ID del balneario
     * @return array|null Datos del balneario o null si no existe
     */
    public function obtenerDatosBalneario($id_balneario) {
        $query = "SELECT * FROM balnearios WHERE id_balneario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
}
?>