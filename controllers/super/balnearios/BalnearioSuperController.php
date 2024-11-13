<?php
class BalnearioSuperController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los balnearios con información adicional
     */
    public function obtenerBalnearios() {
        $query = "SELECT 
                    b.*,
                    (SELECT COUNT(*) FROM usuarios u WHERE u.id_balneario = b.id_balneario) as total_usuarios,
                    (SELECT COUNT(*) FROM eventos e WHERE e.id_balneario = b.id_balneario) as total_eventos,
                    (SELECT COUNT(*) FROM promociones p WHERE p.id_balneario = b.id_balneario) as total_promociones,
                    (SELECT COUNT(*) FROM opiniones_usuarios o WHERE o.id_balneario = b.id_balneario) as total_opiniones,
                    (SELECT COUNT(*) FROM reservaciones r WHERE r.id_balneario = b.id_balneario) as total_reservaciones
                 FROM balnearios b
                 ORDER BY b.nombre_balneario";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los detalles completos de un balneario específico
     */
    public function obtenerDetallesBalneario($id_balneario) {
        // Información básica del balneario
        $query = "SELECT * FROM balnearios WHERE id_balneario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $balneario = $stmt->get_result()->fetch_assoc();

        if (!$balneario) {
            return false;
        }

        // Obtener servicios
        $query = "SELECT s.* 
                 FROM servicios s
                 INNER JOIN detalles_servicios ds ON s.id_servicio = ds.id_servicio
                 WHERE ds.id_balneario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $balneario['servicios'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Obtener eventos activos
        $query = "SELECT * FROM eventos 
                 WHERE id_balneario = ? 
                 AND fecha_fin_evento >= CURDATE()
                 ORDER BY fecha_inicio_evento";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $balneario['eventos'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Obtener promociones activas
        $query = "SELECT * FROM promociones 
                 WHERE id_balneario = ? 
                 AND fecha_fin_promocion >= CURDATE()
                 ORDER BY fecha_inicio_promocion";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $balneario['promociones'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Obtener estadísticas de opiniones
        $query = "SELECT 
                    COUNT(*) as total_opiniones,
                    AVG(valoracion) as valoracion_promedio,
                    COUNT(CASE WHEN opinion_validada = 1 THEN 1 END) as opiniones_validadas
                 FROM opiniones_usuarios
                 WHERE id_balneario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $balneario['estadisticas_opiniones'] = $stmt->get_result()->fetch_assoc();

        // Obtener usuarios asociados
        $query = "SELECT id_usuario, nombre_usuario, email_usuario, fecha_registro 
                 FROM usuarios 
                 WHERE id_balneario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $balneario['usuarios'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return $balneario;
    }

    /**
     * Crea un nuevo balneario
     */
    public function crearBalneario($datos) {
        try {
            $this->conn->begin_transaction();

            $query = "INSERT INTO balnearios (
                        nombre_balneario, descripcion_balneario, direccion_balneario,
                        horario_apertura, horario_cierre, telefono_balneario,
                        email_balneario, facebook_balneario, instagram_balneario,
                        x_balneario, tiktok_balneario, precio_general
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "sssssssssssd",
                $datos['nombre_balneario'],
                $datos['descripcion_balneario'],
                $datos['direccion_balneario'],
                $datos['horario_apertura'],
                $datos['horario_cierre'],
                $datos['telefono_balneario'],
                $datos['email_balneario'],
                $datos['facebook_balneario'],
                $datos['instagram_balneario'],
                $datos['x_balneario'],
                $datos['tiktok_balneario'],
                $datos['precio_general']
            );

            if (!$stmt->execute()) {
                throw new Exception("Error al crear el balneario");
            }

            $id_balneario = $this->conn->insert_id;
            $this->conn->commit();
            return $id_balneario;

        } catch (Exception $e) {
            $this->conn->rollback();
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Actualiza los datos de un balneario
     */
    public function actualizarBalneario($id_balneario, $datos) {
        try {
            $query = "UPDATE balnearios SET 
                        nombre_balneario = ?, 
                        descripcion_balneario = ?,
                        direccion_balneario = ?,
                        horario_apertura = ?,
                        horario_cierre = ?,
                        telefono_balneario = ?,
                        email_balneario = ?,
                        facebook_balneario = ?,
                        instagram_balneario = ?,
                        x_balneario = ?,
                        tiktok_balneario = ?,
                        precio_general = ?
                     WHERE id_balneario = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "sssssssssssdi",
                $datos['nombre_balneario'],
                $datos['descripcion_balneario'],
                $datos['direccion_balneario'],
                $datos['horario_apertura'],
                $datos['horario_cierre'],
                $datos['telefono_balneario'],
                $datos['email_balneario'],
                $datos['facebook_balneario'],
                $datos['instagram_balneario'],
                $datos['x_balneario'],
                $datos['tiktok_balneario'],
                $datos['precio_general'],
                $id_balneario
            );

            return $stmt->execute();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
?> 