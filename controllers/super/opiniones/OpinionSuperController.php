<?php
class OpinionSuperController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todas las opiniones con información del balneario
     * @param array $filtros Filtros opcionales (balneario, estado_validacion, fecha)
     */
    public function obtenerOpiniones($filtros = []) {
        $query = "SELECT o.*, b.nombre_balneario,
                 CASE 
                    WHEN o.opinion_validada IS NULL THEN 'pendiente'
                    WHEN o.opinion_validada = 1 THEN 'validada'
                    ELSE 'invalidada'
                 END as estado_validacion
                 FROM opiniones_usuarios o
                 INNER JOIN balnearios b ON o.id_balneario = b.id_balneario
                 WHERE 1=1";
        
        $params = [];
        $types = "";

        // Aplicar filtros
        if (!empty($filtros['id_balneario'])) {
            $query .= " AND o.id_balneario = ?";
            $params[] = $filtros['id_balneario'];
            $types .= "i";
        }

        if (isset($filtros['estado_validacion'])) {
            switch ($filtros['estado_validacion']) {
                case 'pendiente':
                    $query .= " AND o.opinion_validada IS NULL";
                    break;
                case 'validada':
                    $query .= " AND o.opinion_validada = 1";
                    break;
                case 'invalidada':
                    $query .= " AND o.opinion_validada = 0";
                    break;
            }
        }

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $query .= " AND DATE(o.fecha_registro_opinion) BETWEEN ? AND ?";
            $params[] = $filtros['fecha_inicio'];
            $params[] = $filtros['fecha_fin'];
            $types .= "ss";
        }

        $query .= " ORDER BY o.fecha_registro_opinion DESC";

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene estadísticas de opiniones
     */
    public function obtenerEstadisticas($id_balneario = null) {
        $stats = [];
        $whereBalneario = $id_balneario ? "WHERE id_balneario = " . intval($id_balneario) : "";
        
        // Total de opiniones por estado
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN opinion_validada = 1 THEN 1 ELSE 0 END) as validadas,
                    SUM(CASE WHEN opinion_validada = 0 THEN 1 ELSE 0 END) as invalidadas,
                    SUM(CASE WHEN opinion_validada IS NULL THEN 1 ELSE 0 END) as pendientes,
                    AVG(valoracion) as promedio_valoracion
                 FROM opiniones_usuarios
                 $whereBalneario";
        
        $result = $this->conn->query($query);
        $stats = $result->fetch_assoc();

        // Balneario con mejor valoración
        if (!$id_balneario) {
            $query = "SELECT b.nombre_balneario, AVG(o.valoracion) as promedio
                     FROM opiniones_usuarios o
                     INNER JOIN balnearios b ON o.id_balneario = b.id_balneario
                     WHERE o.opinion_validada = 1
                     GROUP BY o.id_balneario
                     ORDER BY promedio DESC
                     LIMIT 1";
            $result = $this->conn->query($query);
            $stats['mejor_balneario'] = $result->fetch_assoc();
        }

        return $stats;
    }

    /**
     * Obtiene los detalles de una opinión específica
     */
    public function obtenerOpinion($id_opinion) {
        $query = "SELECT o.*, b.nombre_balneario,
                 CASE 
                    WHEN o.opinion_validada IS NULL THEN 'pendiente'
                    WHEN o.opinion_validada = 1 THEN 'validada'
                    ELSE 'invalidada'
                 END as estado_validacion
                 FROM opiniones_usuarios o
                 INNER JOIN balnearios b ON o.id_balneario = b.id_balneario
                 WHERE o.id_opinion = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_opinion);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Valida o invalida una opinión
     */
    public function validarOpinion($id_opinion, $validar = true) {
        try {
            $query = "UPDATE opiniones_usuarios 
                     SET opinion_validada = ?
                     WHERE id_opinion = ?";
            
            $validacion = $validar ? 1 : 0;
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $validacion, $id_opinion);
            
            return $stmt->execute();

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Obtiene la lista de balnearios para el filtro
     */
    public function obtenerBalnearios() {
        $query = "SELECT id_balneario, nombre_balneario 
                 FROM balnearios 
                 ORDER BY nombre_balneario";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los detalles de un balneario
     */
    public function obtenerBalneario($id_balneario) {
        $query = "SELECT id_balneario, nombre_balneario 
                 FROM balnearios 
                 WHERE id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?> 