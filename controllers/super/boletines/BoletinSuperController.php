<?php
class BoletinSuperController {
    private $conn;
    private $usuario_id;

    public function __construct($db, $usuario_id) {
        $this->conn = $db;
        $this->usuario_id = $usuario_id;
    }

    /**
     * Obtiene los boletines del sistema creados por superadministradores
     */
    public function obtenerBoletinesSistema($filtros = []) {
        try {
            $query = "SELECT b.*, u.nombre_usuario, u.rol_usuario,
                     CASE 
                        WHEN b.fecha_envio_boletin IS NULL THEN 'borrador'
                        ELSE 'enviado'
                     END as estado_boletin
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE u.rol_usuario = 'superadministrador'";

            $params = [];
            $types = "";

            // Filtro por estado
            if (!empty($filtros['estado'])) {
                if ($filtros['estado'] === 'borrador') {
                    $query .= " AND b.fecha_envio_boletin IS NULL";
                } else if ($filtros['estado'] === 'enviado') {
                    $query .= " AND b.fecha_envio_boletin IS NOT NULL";
                }
            }

            // Filtro por creador (mis boletines)
            if (!empty($filtros['creador']) && $filtros['creador'] === 'propio') {
                $query .= " AND b.id_usuario = ?";
                $params[] = $this->usuario_id;
                $types .= "i";
            }

            $query .= " ORDER BY b.fecha_envio_boletin DESC, b.id_boletin DESC";

            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerBoletinesSistema: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un boletín específico
     */
    public function obtenerBoletin($id_boletin) {
        try {
            $query = "SELECT b.*, u.nombre_usuario, u.rol_usuario,
                     CASE 
                        WHEN b.fecha_envio_boletin IS NULL THEN 'borrador'
                        ELSE 'enviado'
                     END as estado_boletin
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE b.id_boletin = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $id_boletin);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_assoc();

        } catch (Exception $e) {
            error_log("Error en obtenerBoletin: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo boletín
     */
    public function crearBoletin($datos) {
        try {
            $this->conn->begin_transaction();

            $query = "INSERT INTO boletines (
                        titulo_boletin, 
                        contenido_boletin,
                        id_usuario
                    ) VALUES (?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param(
                "ssi",
                $datos['titulo'],
                $datos['contenido'],
                $this->usuario_id
            );

            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $id_boletin = $this->conn->insert_id;
            $this->conn->commit();

            return [
                'success' => true,
                'id_boletin' => $id_boletin,
                'message' => 'Boletín creado exitosamente'
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error en crearBoletin: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene estadísticas de boletines del sistema
     */
    public function obtenerEstadisticas() {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN fecha_envio_boletin IS NULL THEN 1 ELSE 0 END) as borradores,
                        SUM(CASE WHEN fecha_envio_boletin IS NOT NULL THEN 1 ELSE 0 END) as enviados
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE u.rol_usuario = 'superadministrador'";

            $result = $this->conn->query($query);
            if (!$result) {
                throw new Exception("Error al obtener estadísticas: " . $this->conn->error);
            }

            return $result->fetch_assoc();

        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return [
                'total' => 0,
                'borradores' => 0,
                'enviados' => 0
            ];
        }
    }
}
?> 