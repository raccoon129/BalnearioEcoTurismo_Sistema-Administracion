<?php
class ReservacionController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene las reservaciones por fecha
     */
    public function obtenerReservacionesPorFecha($id_balneario, $fecha) {
        $query = "SELECT * FROM reservaciones 
                 WHERE id_balneario = ? AND fecha_reserva = ?
                 ORDER BY hora_reserva";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $id_balneario, $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene el resumen de reservaciones por fecha
     */
    public function obtenerResumenPorFecha($id_balneario, $fecha) {
        $query = "SELECT 
                 COUNT(*) as total_reservaciones,
                 SUM(cantidad_adultos) as total_adultos,
                 SUM(cantidad_ninos) as total_ninos
                 FROM reservaciones 
                 WHERE id_balneario = ? AND fecha_reserva = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $id_balneario, $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene los detalles de una reservación específica
     */
    public function obtenerReservacion($id_reservacion, $id_balneario) {
        $query = "SELECT * FROM reservaciones 
                 WHERE id_reservacion = ? AND id_balneario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_reservacion, $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene estadísticas de reservaciones
     */
    public function obtenerEstadisticas($id_balneario) {
        $query = "SELECT 
                 DATE_FORMAT(fecha_reserva, '%Y-%m') as mes,
                 COUNT(*) as total_reservaciones,
                 SUM(cantidad_adultos + cantidad_ninos) as total_visitantes
                 FROM reservaciones 
                 WHERE id_balneario = ? 
                 AND fecha_reserva >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY DATE_FORMAT(fecha_reserva, '%Y-%m')
                 ORDER BY mes DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?> 