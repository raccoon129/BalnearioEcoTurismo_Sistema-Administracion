<?php
class PromocionSuperController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todas las promociones con información del balneario
     * @param array $filtros Filtros opcionales (balneario, estado, fecha)
     */
    public function obtenerPromociones($filtros = []) {
        $query = "SELECT p.*, b.nombre_balneario,
                 CASE 
                    WHEN p.fecha_fin_promocion < CURDATE() THEN 'finalizada'
                    WHEN p.fecha_inicio_promocion > CURDATE() THEN 'proxima'
                    ELSE 'activa'
                 END as estado_promocion
                 FROM promociones p
                 INNER JOIN balnearios b ON p.id_balneario = b.id_balneario
                 WHERE 1=1";
        
        $params = [];
        $types = "";

        // Aplicar filtros
        if (!empty($filtros['id_balneario'])) {
            $query .= " AND p.id_balneario = ?";
            $params[] = $filtros['id_balneario'];
            $types .= "i";
        }

        if (!empty($filtros['estado'])) {
            switch ($filtros['estado']) {
                case 'activa':
                    $query .= " AND p.fecha_inicio_promocion <= CURDATE() AND p.fecha_fin_promocion >= CURDATE()";
                    break;
                case 'finalizada':
                    $query .= " AND p.fecha_fin_promocion < CURDATE()";
                    break;
                case 'proxima':
                    $query .= " AND p.fecha_inicio_promocion > CURDATE()";
                    break;
            }
        }

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $query .= " AND (
                (p.fecha_inicio_promocion BETWEEN ? AND ?) OR
                (p.fecha_fin_promocion BETWEEN ? AND ?) OR
                (p.fecha_inicio_promocion <= ? AND p.fecha_fin_promocion >= ?)
            )";
            $params = array_merge($params, [
                $filtros['fecha_inicio'], $filtros['fecha_fin'],
                $filtros['fecha_inicio'], $filtros['fecha_fin'],
                $filtros['fecha_inicio'], $filtros['fecha_fin']
            ]);
            $types .= "ssssss";
        }

        $query .= " ORDER BY 
                   CASE 
                        WHEN p.fecha_inicio_promocion > CURDATE() THEN 1
                        WHEN p.fecha_fin_promocion >= CURDATE() THEN 2
                        ELSE 3
                   END,
                   p.fecha_inicio_promocion DESC";

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los detalles de una promoción específica
     */
    public function obtenerPromocion($id_promocion) {
        $query = "SELECT p.*, b.nombre_balneario,
                 CASE 
                    WHEN p.fecha_fin_promocion < CURDATE() THEN 'finalizada'
                    WHEN p.fecha_inicio_promocion > CURDATE() THEN 'proxima'
                    ELSE 'activa'
                 END as estado_promocion
                 FROM promociones p
                 INNER JOIN balnearios b ON p.id_balneario = b.id_balneario
                 WHERE p.id_promocion = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_promocion);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
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
     * Obtiene estadísticas de promociones
     */
    public function obtenerEstadisticas() {
        $stats = [];
        
        // Total de promociones activas
        $query = "SELECT COUNT(*) as total FROM promociones 
                 WHERE fecha_inicio_promocion <= CURDATE() 
                 AND fecha_fin_promocion >= CURDATE()";
        $result = $this->conn->query($query);
        $stats['activas'] = $result->fetch_assoc()['total'];

        // Total de promociones próximas
        $query = "SELECT COUNT(*) as total FROM promociones 
                 WHERE fecha_inicio_promocion > CURDATE()";
        $result = $this->conn->query($query);
        $stats['proximas'] = $result->fetch_assoc()['total'];

        // Total de promociones finalizadas
        $query = "SELECT COUNT(*) as total FROM promociones 
                 WHERE fecha_fin_promocion < CURDATE()";
        $result = $this->conn->query($query);
        $stats['finalizadas'] = $result->fetch_assoc()['total'];

        // Balneario con más promociones activas
        $query = "SELECT b.nombre_balneario, COUNT(*) as total
                 FROM promociones p
                 INNER JOIN balnearios b ON p.id_balneario = b.id_balneario
                 WHERE p.fecha_inicio_promocion <= CURDATE() 
                 AND p.fecha_fin_promocion >= CURDATE()
                 GROUP BY p.id_balneario
                 ORDER BY total DESC
                 LIMIT 1";
        $result = $this->conn->query($query);
        $stats['balneario_mas_activo'] = $result->fetch_assoc();

        return $stats;
    }

    /**
     * Crea una nueva promoción
     */
    public function crearPromocion($datos) {
        try {
            $query = "INSERT INTO promociones (
                        titulo_promocion, descripcion_promocion,
                        fecha_inicio_promocion, fecha_fin_promocion,
                        id_balneario
                    ) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "ssssi",
                $datos['titulo_promocion'],
                $datos['descripcion_promocion'],
                $datos['fecha_inicio_promocion'],
                $datos['fecha_fin_promocion'],
                $datos['id_balneario']
            );

            return $stmt->execute();

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Actualiza una promoción existente
     */
    public function actualizarPromocion($id_promocion, $datos) {
        try {
            $query = "UPDATE promociones SET 
                        titulo_promocion = ?,
                        descripcion_promocion = ?,
                        fecha_inicio_promocion = ?,
                        fecha_fin_promocion = ?,
                        id_balneario = ?
                     WHERE id_promocion = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "ssssii",
                $datos['titulo_promocion'],
                $datos['descripcion_promocion'],
                $datos['fecha_inicio_promocion'],
                $datos['fecha_fin_promocion'],
                $datos['id_balneario'],
                $id_promocion
            );

            return $stmt->execute();

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Elimina una promoción
     */
    public function eliminarPromocion($id_promocion) {
        try {
            $query = "DELETE FROM promociones WHERE id_promocion = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_promocion);
            
            if ($stmt->execute()) {
                return true;
            }

            throw new Exception("Error al eliminar la promoción");

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Verifica si una promoción puede ser editada
     */
    public function puedeEditarPromocion($id_promocion) {
        $query = "SELECT fecha_fin_promocion FROM promociones WHERE id_promocion = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_promocion);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            return false;
        }

        return strtotime($result['fecha_fin_promocion']) >= strtotime('today');
    }

    /**
     * Obtiene estadísticas detalladas de una promoción
     */
    public function obtenerEstadisticasPromocion($id_promocion) {
        $query = "SELECT p.*, b.nombre_balneario,
                 DATEDIFF(p.fecha_fin_promocion, p.fecha_inicio_promocion) as duracion_dias,
                 CASE 
                    WHEN p.fecha_fin_promocion < CURDATE() THEN 'finalizada'
                    WHEN p.fecha_inicio_promocion > CURDATE() THEN 'proxima'
                    ELSE 'activa'
                 END as estado
                 FROM promociones p
                 INNER JOIN balnearios b ON p.id_balneario = b.id_balneario
                 WHERE p.id_promocion = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_promocion);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?> 