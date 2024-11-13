<?php
class ResumenController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene las últimas opiniones registradas en todos los balnearios
     */
    public function obtenerUltimasOpiniones() {
        $query = "SELECT o.*, b.nombre_balneario 
                 FROM opiniones_usuarios o
                 INNER JOIN balnearios b ON o.id_balneario = b.id_balneario
                 ORDER BY o.fecha_registro_opinion DESC 
                 LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los próximos eventos de todos los balnearios
     */
    public function obtenerProximosEventos() {
        $query = "SELECT e.*, b.nombre_balneario 
                 FROM eventos e
                 INNER JOIN balnearios b ON e.id_balneario = b.id_balneario
                 WHERE e.fecha_inicio_evento >= CURDATE()
                 ORDER BY e.fecha_inicio_evento ASC 
                 LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene las últimas reservaciones realizadas
     */
    public function obtenerUltimasReservaciones() {
        $query = "SELECT r.*, b.nombre_balneario 
                 FROM reservaciones r
                 INNER JOIN balnearios b ON r.id_balneario = b.id_balneario
                 ORDER BY r.fecha_realizacion_reserva DESC 
                 LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene estadísticas generales del sistema
     */
    public function obtenerEstadisticas() {
        $stats = [];

        // Total de balnearios
        $query = "SELECT COUNT(*) as total FROM balnearios";
        $result = $this->conn->query($query);
        $stats['total_balnearios'] = $result->fetch_assoc()['total'];

        // Total de opiniones esta semana
        $query = "SELECT COUNT(*) as total 
                 FROM opiniones_usuarios 
                 WHERE fecha_registro_opinion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $result = $this->conn->query($query);
        $stats['opiniones_semana'] = $result->fetch_assoc()['total'];

        // Total de reservaciones esta semana
        $query = "SELECT COUNT(*) as total 
                 FROM reservaciones 
                 WHERE fecha_realizacion_reserva >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $result = $this->conn->query($query);
        $stats['reservaciones_semana'] = $result->fetch_assoc()['total'];

        // Total de eventos activos
        $query = "SELECT COUNT(*) as total 
                 FROM eventos 
                 WHERE fecha_fin_evento >= CURDATE()";
        $result = $this->conn->query($query);
        $stats['eventos_activos'] = $result->fetch_assoc()['total'];

        return $stats;
    }
}
?> 