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

    /**
     * Obtiene todos los boletines de todos los balnearios
     */
    public function obtenerTodosBoletinesBalnearios($filtros = []) {
        try {
            $query = "SELECT b.*, u.nombre_usuario, u.rol_usuario, bal.nombre_balneario,
                     CASE 
                        WHEN b.fecha_envio_boletin IS NULL THEN 'borrador'
                        ELSE 'enviado'
                     END as estado_boletin
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     INNER JOIN balnearios bal ON b.id_balneario = bal.id_balneario
                     WHERE 1=1";

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

            // Filtro por balneario específico
            if (!empty($filtros['id_balneario'])) {
                $query .= " AND b.id_balneario = ?";
                $params[] = $filtros['id_balneario'];
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
            error_log("Error en obtenerTodosBoletinesBalnearios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de boletines por balneario
     */
    public function obtenerEstadisticasBalneario($id_balneario = null) {
        try {
            $whereClause = $id_balneario ? "WHERE b.id_balneario = " . intval($id_balneario) : "";

            $query = "SELECT 
                        bal.id_balneario,
                        bal.nombre_balneario,
                        COUNT(*) as total_boletines,
                        SUM(CASE WHEN b.fecha_envio_boletin IS NULL THEN 1 ELSE 0 END) as borradores,
                        SUM(CASE WHEN b.fecha_envio_boletin IS NOT NULL THEN 1 ELSE 0 END) as enviados
                     FROM boletines b
                     INNER JOIN balnearios bal ON b.id_balneario = bal.id_balneario
                     $whereClause
                     GROUP BY bal.id_balneario, bal.nombre_balneario
                     ORDER BY total_boletines DESC";

            $result = $this->conn->query($query);
            if (!$result) {
                throw new Exception("Error al obtener estadísticas: " . $this->conn->error);
            }

            if ($id_balneario) {
                return $result->fetch_assoc();
            }

            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticasBalneario: " . $e->getMessage());
            return $id_balneario ? null : [];
        }
    }

    /**
     * Obtiene la lista de balnearios disponibles
     */
    public function obtenerBalnearios() {
        try {
            $query = "SELECT b.*, 
                     (SELECT COUNT(*) FROM boletines WHERE id_balneario = b.id_balneario) as total_boletines
                     FROM balnearios b 
                     ORDER BY b.nombre_balneario";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerBalnearios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los destinatarios según el tipo especificado
     */
    public function obtenerDestinatarios($tipo, $id_balneario = null) {
        try {
            $query = "";
            $params = [];
            $types = "";

            switch ($tipo) {
                case 'superadmin':
                    $query = "SELECT DISTINCT email_usuario, nombre_usuario 
                             FROM usuarios 
                             WHERE rol_usuario = 'superadministrador'";
                    break;

                case 'admin':
                    $query = "SELECT DISTINCT email_usuario, nombre_usuario 
                             FROM usuarios 
                             WHERE rol_usuario = 'administrador_balneario'";
                    if ($id_balneario) {
                        $query .= " AND id_balneario = ?";
                        $params[] = $id_balneario;
                        $types .= "i";
                    }
                    break;

                case 'suscriptores':
                    $query = "SELECT DISTINCT email_usuario, nombre_usuario 
                             FROM opiniones_usuarios 
                             WHERE suscripcion_boletin = 1 
                             AND email_usuario IS NOT NULL";
                    if ($id_balneario) {
                        $query .= " AND id_balneario = ?";
                        $params[] = $id_balneario;
                        $types .= "i";
                    }
                    break;

                default:
                    return [];
            }

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
            error_log("Error en obtenerDestinatarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza el estado de un boletín
     */
    public function actualizarEstado($id_boletin, $estado) {
        try {
            $query = "UPDATE boletines 
                     SET fecha_envio_boletin = CASE 
                         WHEN ? = 'enviado' THEN CURRENT_TIMESTAMP 
                         ELSE NULL 
                     END
                     WHERE id_boletin = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("si", $estado, $id_boletin);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            return true;

        } catch (Exception $e) {
            error_log("Error en actualizarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la información de un balneario específico
     */
    public function obtenerBalneario($id_balneario) {
        try {
            $query = "SELECT b.id_balneario, b.nombre_balneario,
                     (SELECT COUNT(*) FROM boletines WHERE id_balneario = b.id_balneario) as total_boletines,
                     (SELECT COUNT(*) FROM boletines 
                      WHERE id_balneario = b.id_balneario 
                      AND fecha_envio_boletin IS NULL) as total_borradores,
                     (SELECT COUNT(*) FROM boletines 
                      WHERE id_balneario = b.id_balneario 
                      AND fecha_envio_boletin IS NOT NULL) as total_enviados
                     FROM balnearios b
                     WHERE b.id_balneario = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $id_balneario);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_assoc();

        } catch (Exception $e) {
            error_log("Error en obtenerBalneario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene los detalles completos de un boletín del sistema
     */
    public function obtenerDetallesBoletinSistema($id_boletin) {
        try {
            $query = "SELECT b.*, u.nombre_usuario, u.rol_usuario,
                     CASE 
                        WHEN b.fecha_envio_boletin IS NULL THEN 'borrador'
                        ELSE 'enviado'
                     END as estado_boletin,
                     b.fecha_envio_boletin as fecha_envio,
                     COALESCE(b.fecha_envio_boletin, b.fecha_creacion) as fecha_creacion
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE b.id_boletin = ? 
                     AND u.rol_usuario = 'superadministrador'";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $id_boletin);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $boletin = $result->fetch_assoc();

            if (!$boletin) {
                return null;
            }

            // Asegurar que todas las claves necesarias existan
            return array_merge([
                'id_boletin' => null,
                'titulo_boletin' => '',
                'contenido_boletin' => '',
                'fecha_creacion' => null,
                'fecha_envio' => null,
                'id_balneario' => null,
                'nombre_usuario' => '',
                'rol_usuario' => '',
                'estado_boletin' => 'borrador'
            ], $boletin);

        } catch (Exception $e) {
            error_log("Error en obtenerDetallesBoletinSistema: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene los boletines creados por superadministradores
     */
    public function obtenerBoletinesSuperAdmin($filtros = []) {
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
            error_log("Error en obtenerBoletinesSuperAdmin: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles de un boletín de superadministrador
     */
    public function obtenerBoletinSuperAdmin($id_boletin) {
        try {
            $query = "SELECT b.*, u.nombre_usuario, u.rol_usuario,
                     CASE 
                        WHEN b.fecha_envio_boletin IS NULL THEN 'borrador'
                        ELSE 'enviado'
                     END as estado_boletin
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE b.id_boletin = ? 
                     AND u.rol_usuario = 'superadministrador'";

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
            error_log("Error en obtenerBoletinSuperAdmin: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene los boletines creados por administradores de balneario
     */
    public function obtenerBoletinesAdminBalneario($filtros = []) {
        try {
            $query = "SELECT b.*, u.nombre_usuario, u.rol_usuario, bal.nombre_balneario,
                     CASE 
                        WHEN b.fecha_envio_boletin IS NULL THEN 'borrador'
                        ELSE 'enviado'
                     END as estado_boletin
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     INNER JOIN balnearios bal ON u.id_balneario = bal.id_balneario
                     WHERE u.rol_usuario = 'administrador_balneario'";

            $params = [];
            $types = "";

            // Filtro por balneario
            if (!empty($filtros['id_balneario'])) {
                $query .= " AND u.id_balneario = ?";
                $params[] = $filtros['id_balneario'];
                $types .= "i";
            }

            // Filtro por estado
            if (!empty($filtros['estado'])) {
                if ($filtros['estado'] === 'borrador') {
                    $query .= " AND b.fecha_envio_boletin IS NULL";
                } else if ($filtros['estado'] === 'enviado') {
                    $query .= " AND b.fecha_envio_boletin IS NOT NULL";
                }
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
            error_log("Error en obtenerBoletinesAdminBalneario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles completos de un boletín de superadministrador
     */
    public function obtenerDetallesBoletinSuperAdmin($id_boletin) {
        try {
            $query = "SELECT b.*, u.nombre_usuario, u.rol_usuario,
                     CASE 
                        WHEN b.fecha_envio_boletin IS NULL THEN 'borrador'
                        ELSE 'enviado'
                     END as estado_boletin,
                     b.fecha_envio_boletin as fecha_envio,
                     COALESCE(b.fecha_envio_boletin, CURRENT_TIMESTAMP) as fecha_creacion,
                     NULL as id_balneario,
                     NULL as nombre_balneario
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE b.id_boletin = ? 
                     AND u.rol_usuario = 'superadministrador'";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $id_boletin);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $boletin = $result->fetch_assoc();

            if (!$boletin) {
                return null;
            }

            // Asegurar que todas las claves necesarias existan
            return array_merge([
                'id_boletin' => null,
                'titulo_boletin' => '',
                'contenido_boletin' => '',
                'fecha_creacion' => null,
                'fecha_envio' => null,
                'id_balneario' => null,
                'nombre_balneario' => null,
                'nombre_usuario' => '',
                'rol_usuario' => '',
                'estado_boletin' => 'borrador'
            ], $boletin);

        } catch (Exception $e) {
            error_log("Error en obtenerDetallesBoletinSuperAdmin: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene los boletines específicos de un balneario
     * (creados por administradores asociados a ese balneario)
     */
    public function obtenerBoletinesDeBalneario($id_balneario, $filtros = []) {
        try {
            $query = "SELECT b.*, u.nombre_usuario, u.rol_usuario,
                     CASE 
                        WHEN b.fecha_envio_boletin IS NULL THEN 'borrador'
                        ELSE 'enviado'
                     END as estado_boletin
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE u.rol_usuario = 'administrador_balneario'
                     AND u.id_balneario = ?";

            $params = [$id_balneario];
            $types = "i";

            // Filtro por estado
            if (!empty($filtros['estado'])) {
                if ($filtros['estado'] === 'borrador') {
                    $query .= " AND b.fecha_envio_boletin IS NULL";
                } else if ($filtros['estado'] === 'enviado') {
                    $query .= " AND b.fecha_envio_boletin IS NOT NULL";
                }
            }

            $query .= " ORDER BY b.fecha_envio_boletin DESC, b.id_boletin DESC";

            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerBoletinesDeBalneario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de boletines de un balneario específico
     */
    public function obtenerEstadisticasBoletinesBalneario($id_balneario) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_boletines,
                        SUM(CASE WHEN b.fecha_envio_boletin IS NULL THEN 1 ELSE 0 END) as borradores,
                        SUM(CASE WHEN b.fecha_envio_boletin IS NOT NULL THEN 1 ELSE 0 END) as enviados
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE u.rol_usuario = 'administrador_balneario'
                     AND u.id_balneario = ?";

            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $id_balneario);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();

            return [
                'total_boletines' => $stats['total_boletines'] ?? 0,
                'borradores' => $stats['borradores'] ?? 0,
                'enviados' => $stats['enviados'] ?? 0
            ];

        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticasBoletinesBalneario: " . $e->getMessage());
            return [
                'total_boletines' => 0,
                'borradores' => 0,
                'enviados' => 0
            ];
        }
    }

    /**
     * Obtiene los detalles completos de un boletín de balneario
     */
    public function obtenerDetallesBoletinBalneario($id_boletin) {
        try {
            $query = "SELECT b.*, u.nombre_usuario, u.rol_usuario, bal.nombre_balneario,
                     CASE 
                        WHEN b.fecha_envio_boletin IS NULL THEN 'borrador'
                        ELSE 'enviado'
                     END as estado_boletin,
                     b.fecha_envio_boletin as fecha_envio,
                     COALESCE(b.fecha_envio_boletin, CURRENT_TIMESTAMP) as fecha_creacion,
                     bal.id_balneario,
                     bal.nombre_balneario
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     INNER JOIN balnearios bal ON u.id_balneario = bal.id_balneario
                     WHERE b.id_boletin = ? 
                     AND u.rol_usuario = 'administrador_balneario'";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $id_boletin);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $boletin = $result->fetch_assoc();

            if (!$boletin) {
                return null;
            }

            // Asegurar que todas las claves necesarias existan
            return array_merge([
                'id_boletin' => null,
                'titulo_boletin' => '',
                'contenido_boletin' => '',
                'fecha_creacion' => null,
                'fecha_envio' => null,
                'id_balneario' => null,
                'nombre_balneario' => '',
                'nombre_usuario' => '',
                'rol_usuario' => '',
                'estado_boletin' => 'borrador'
            ], $boletin);

        } catch (Exception $e) {
            error_log("Error en obtenerDetallesBoletinBalneario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene los correos de todos los superadministradores
     * @return array Lista de correos y nombres de superadministradores
     */
    public function obtenerCorreosSuperAdmin() {
        try {
            $query = "SELECT DISTINCT email_usuario, nombre_usuario 
                     FROM usuarios 
                     WHERE rol_usuario = 'superadministrador'
                     AND email_usuario IS NOT NULL";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta");
            }

            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta");
            }

            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerCorreosSuperAdmin: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los correos de administradores de balnearios
     * @param int|null $id_balneario ID del balneario específico (opcional)
     * @return array Lista de correos y nombres de administradores
     */
    public function obtenerCorreosAdminBalnearios($id_balneario = null) {
        try {
            $query = "SELECT DISTINCT email_usuario, nombre_usuario 
                     FROM usuarios 
                     WHERE rol_usuario = 'administrador_balneario'
                     AND email_usuario IS NOT NULL";

            $params = [];
            $types = "";

            if ($id_balneario) {
                $query .= " AND id_balneario = ?";
                $params[] = $id_balneario;
                $types .= "i";
            }

            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta");
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta");
            }

            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerCorreosAdminBalnearios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los correos de usuarios suscritos al boletín
     * @param int|null $id_balneario ID del balneario específico (opcional)
     * @return array Lista de correos y nombres de suscriptores
     */
    public function obtenerCorreosSuscriptores($id_balneario = null) {
        try {
            $query = "SELECT DISTINCT email_usuario, nombre_usuario 
                     FROM opiniones_usuarios 
                     WHERE suscripcion_boletin = 1 
                     AND email_usuario IS NOT NULL";

            $params = [];
            $types = "";

            if ($id_balneario) {
                $query .= " AND id_balneario = ?";
                $params[] = $id_balneario;
                $types .= "i";
            }

            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta");
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta");
            }

            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerCorreosSuscriptores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la lista completa de destinatarios potenciales
     * @return array Lista categorizada de destinatarios
     */
    public function obtenerListaDestinatarios() {
        try {
            // Obtener superadministradores
            $querySuperAdmin = "SELECT 
                               u.email_usuario,
                               u.nombre_usuario,
                               'Superadministrador' as tipo,
                               NULL as balneario
                               FROM usuarios u
                               WHERE u.rol_usuario = 'superadministrador'
                               AND u.email_usuario IS NOT NULL
                               ORDER BY u.nombre_usuario";

            // Obtener administradores de balneario
            $queryAdminBalneario = "SELECT 
                                   u.email_usuario,
                                   u.nombre_usuario,
                                   'Administrador de Balneario' as tipo,
                                   b.nombre_balneario as balneario
                                   FROM usuarios u
                                   INNER JOIN balnearios b ON u.id_balneario = b.id_balneario
                                   WHERE u.rol_usuario = 'administrador_balneario'
                                   AND u.email_usuario IS NOT NULL
                                   ORDER BY b.nombre_balneario, u.nombre_usuario";

            // Obtener suscriptores
            $querySuscriptores = "SELECT 
                                 o.email_usuario,
                                 o.nombre_usuario,
                                 'Suscriptor' as tipo,
                                 b.nombre_balneario as balneario
                                 FROM opiniones_usuarios o
                                 LEFT JOIN balnearios b ON o.id_balneario = b.id_balneario
                                 WHERE o.suscripcion_boletin = 1
                                 AND o.email_usuario IS NOT NULL
                                 ORDER BY b.nombre_balneario, o.nombre_usuario";

            // Ejecutar consultas
            $superadmins = $this->ejecutarConsulta($querySuperAdmin);
            $admins = $this->ejecutarConsulta($queryAdminBalneario);
            $suscriptores = $this->ejecutarConsulta($querySuscriptores);

            // Agrupar resultados
            return [
                'superadministradores' => $superadmins,
                'administradores' => $admins,
                'suscriptores' => $suscriptores,
                'totales' => [
                    'superadministradores' => count($superadmins),
                    'administradores' => count($admins),
                    'suscriptores' => count($suscriptores),
                    'total' => count($superadmins) + count($admins) + count($suscriptores)
                ]
            ];

        } catch (Exception $e) {
            error_log("Error en obtenerListaDestinatarios: " . $e->getMessage());
            return [
                'superadministradores' => [],
                'administradores' => [],
                'suscriptores' => [],
                'totales' => [
                    'superadministradores' => 0,
                    'administradores' => 0,
                    'suscriptores' => 0,
                    'total' => 0
                ]
            ];
        }
    }

    /**
     * Método auxiliar para ejecutar consultas
     */
    private function ejecutarConsulta($query) {
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    


}
?> 