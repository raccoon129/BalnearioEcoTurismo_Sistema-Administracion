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
     * Obtiene estadísticas generales del balneario
     */
    public function obtenerEstadisticasGenerales($id_balneario) {
        try {
            $stats = [];

            // Reservas de hoy
            $query = "SELECT COUNT(*) as total FROM reservaciones 
                     WHERE id_balneario = ? 
                     AND DATE(fecha_reserva) = CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['reservas_hoy'] = $result->fetch_assoc()['total'];

            // Valoración promedio
            $query = "SELECT AVG(valoracion) as promedio 
                     FROM opiniones_usuarios 
                     WHERE id_balneario = ? 
                     AND opinion_validada = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['valoracion_promedio'] = $result->fetch_assoc()['promedio'] ?? 0;

            // Eventos activos
            $query = "SELECT COUNT(*) as total FROM eventos 
                     WHERE id_balneario = ? 
                     AND fecha_fin_evento >= CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['eventos_activos'] = $result->fetch_assoc()['total'];

            // Promociones activas
            $query = "SELECT COUNT(*) as total FROM promociones 
                     WHERE id_balneario = ? 
                     AND fecha_fin_promocion >= CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['promociones_activas'] = $result->fetch_assoc()['total'];

            return $stats;

        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticasGenerales: " . $e->getMessage());
            return [
                'reservas_hoy' => 0,
                'valoracion_promedio' => 0,
                'eventos_activos' => 0,
                'promociones_activas' => 0
            ];
        }
    }

    /**
     * Obtiene las reservaciones próximas a partir de la fecha actual
     */
    public function obtenerReservasProximas($id_balneario, $limite = 5) {
        try {
            $query = "SELECT * FROM reservaciones 
                     WHERE id_balneario = ? 
                     AND fecha_reserva >= CURDATE()
                     ORDER BY fecha_reserva ASC, hora_reserva ASC 
                     LIMIT ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $id_balneario, $limite);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en obtenerReservasProximas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las opiniones registradas en los últimos 7 días
     */
    public function obtenerOpinionesRecientes($id_balneario) {
        try {
            $query = "SELECT * FROM opiniones_usuarios 
                     WHERE id_balneario = ? 
                     AND fecha_registro_opinion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                     ORDER BY fecha_registro_opinion DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en obtenerOpinionesRecientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la información básica del balneario
     */
    public function obtenerDatosBalneario($id_balneario) {
        try {
            $query = "SELECT * FROM balnearios WHERE id_balneario = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Error en obtenerDatosBalneario: " . $e->getMessage());
            return null;
        }
    }
}
?>