<?php
class EventoSuperController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los eventos con información del balneario
     * @param array $filtros Filtros opcionales (balneario, estado, fecha)
     */
    public function obtenerEventos($filtros = []) {
        $query = "SELECT e.*, b.nombre_balneario,
                 CASE 
                    WHEN e.fecha_fin_evento < CURDATE() THEN 'finalizado'
                    WHEN e.fecha_inicio_evento > CURDATE() THEN 'proximo'
                    ELSE 'activo'
                 END as estado_evento
                 FROM eventos e
                 INNER JOIN balnearios b ON e.id_balneario = b.id_balneario
                 WHERE 1=1";
        
        $params = [];
        $types = "";

        // Aplicar filtros
        if (!empty($filtros['id_balneario'])) {
            $query .= " AND e.id_balneario = ?";
            $params[] = $filtros['id_balneario'];
            $types .= "i";
        }

        if (!empty($filtros['estado'])) {
            switch ($filtros['estado']) {
                case 'activo':
                    $query .= " AND e.fecha_inicio_evento <= CURDATE() AND e.fecha_fin_evento >= CURDATE()";
                    break;
                case 'finalizado':
                    $query .= " AND e.fecha_fin_evento < CURDATE()";
                    break;
                case 'proximo':
                    $query .= " AND e.fecha_inicio_evento > CURDATE()";
                    break;
            }
        }

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $query .= " AND (
                (e.fecha_inicio_evento BETWEEN ? AND ?) OR
                (e.fecha_fin_evento BETWEEN ? AND ?) OR
                (e.fecha_inicio_evento <= ? AND e.fecha_fin_evento >= ?)
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
                        WHEN e.fecha_inicio_evento > CURDATE() THEN 1
                        WHEN e.fecha_fin_evento >= CURDATE() THEN 2
                        ELSE 3
                   END,
                   e.fecha_inicio_evento DESC";

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene estadísticas de eventos
     */
    public function obtenerEstadisticas() {
        $stats = [];
        
        // Total de eventos activos
        $query = "SELECT COUNT(*) as total FROM eventos 
                 WHERE fecha_inicio_evento <= CURDATE() 
                 AND fecha_fin_evento >= CURDATE()";
        $result = $this->conn->query($query);
        $stats['activos'] = $result->fetch_assoc()['total'];

        // Total de eventos próximos
        $query = "SELECT COUNT(*) as total FROM eventos 
                 WHERE fecha_inicio_evento > CURDATE()";
        $result = $this->conn->query($query);
        $stats['proximos'] = $result->fetch_assoc()['total'];

        // Total de eventos finalizados
        $query = "SELECT COUNT(*) as total FROM eventos 
                 WHERE fecha_fin_evento < CURDATE()";
        $result = $this->conn->query($query);
        $stats['finalizados'] = $result->fetch_assoc()['total'];

        // Balneario con más eventos activos
        $query = "SELECT b.nombre_balneario, COUNT(*) as total
                 FROM eventos e
                 INNER JOIN balnearios b ON e.id_balneario = b.id_balneario
                 WHERE e.fecha_inicio_evento <= CURDATE() 
                 AND e.fecha_fin_evento >= CURDATE()
                 GROUP BY e.id_balneario
                 ORDER BY total DESC
                 LIMIT 1";
        $result = $this->conn->query($query);
        $stats['balneario_mas_activo'] = $result->fetch_assoc();

        return $stats;
    }

    /**
     * Obtiene los balnearios para el filtro
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
     * Obtiene un evento específico
     */
    public function obtenerEvento($id_evento) {
        $query = "SELECT e.*, b.nombre_balneario,
                 CASE 
                    WHEN e.fecha_fin_evento < CURDATE() THEN 'finalizado'
                    WHEN e.fecha_inicio_evento > CURDATE() THEN 'proximo'
                    ELSE 'activo'
                 END as estado_evento,
                 DATEDIFF(e.fecha_fin_evento, e.fecha_inicio_evento) as duracion_dias
                 FROM eventos e
                 INNER JOIN balnearios b ON e.id_balneario = b.id_balneario
                 WHERE e.id_evento = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_evento);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Crea un nuevo evento
     */
    public function crearEvento($datos, $imagen = null) {
        try {
            $this->conn->begin_transaction();

            $query = "INSERT INTO eventos (
                        titulo_evento, descripcion_evento,
                        fecha_inicio_evento, fecha_fin_evento,
                        id_balneario, url_imagen_evento
                    ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "ssssss",
                $datos['titulo_evento'],
                $datos['descripcion_evento'],
                $datos['fecha_inicio_evento'],
                $datos['fecha_fin_evento'],
                $datos['id_balneario'],
                $imagen
            );

            if (!$stmt->execute()) {
                throw new Exception("Error al crear el evento");
            }

            $id_evento = $this->conn->insert_id;
            $this->conn->commit();
            return $id_evento;

        } catch (Exception $e) {
            $this->conn->rollback();
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Actualiza un evento existente
     */
    public function actualizarEvento($id_evento, $datos, $imagen = null) {
        try {
            // Verificar que el evento existe y puede ser editado
            if (!$this->puedeEditarEvento($id_evento)) {
                throw new Exception("El evento no puede ser editado");
            }

            $query = "UPDATE eventos SET 
                        titulo_evento = ?,
                        descripcion_evento = ?,
                        fecha_inicio_evento = ?,
                        fecha_fin_evento = ?,
                        id_balneario = ?" .
                        ($imagen !== null ? ", url_imagen_evento = ?" : "") .
                     " WHERE id_evento = ?";
            
            $params = [
                $datos['titulo_evento'],
                $datos['descripcion_evento'],
                $datos['fecha_inicio_evento'],
                $datos['fecha_fin_evento'],
                $datos['id_balneario']
            ];

            if ($imagen !== null) {
                $params[] = $imagen;
            }
            $params[] = $id_evento;

            $types = "ssssi" . ($imagen !== null ? "s" : "") . "i";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);

            return $stmt->execute();

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Elimina un evento
     */
    public function eliminarEvento($id_evento) {
        try {
            // Verificar que el evento existe y puede ser eliminado
            if (!$this->puedeEditarEvento($id_evento)) {
                throw new Exception("El evento no puede ser eliminado");
            }

            // Obtener la URL de la imagen para eliminarla si existe
            $query = "SELECT url_imagen_evento FROM eventos WHERE id_evento = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_evento);
            $stmt->execute();
            $resultado = $stmt->get_result()->fetch_assoc();

            // Eliminar el evento
            $query = "DELETE FROM eventos WHERE id_evento = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_evento);
            
            if ($stmt->execute()) {
                // Si había una imagen, devolver su URL para eliminarla del servidor
                return $resultado['url_imagen_evento'] ?? true;
            }

            throw new Exception("Error al eliminar el evento");

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Verifica si un evento puede ser editado
     */
    public function puedeEditarEvento($id_evento) {
        $query = "SELECT fecha_fin_evento FROM eventos WHERE id_evento = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_evento);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            return false;
        }

        // Permitir editar si el evento no ha finalizado
        return strtotime($result['fecha_fin_evento']) >= strtotime('today');
    }

    /**
     * Procesa y guarda una imagen de evento
     */
    public function procesarImagen($archivo, $id_evento = null) {
        try {
            $directorio = "../../../assets/img/eventos/";
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];

            // Validar extensión
            if (!in_array($extension, $extensionesPermitidas)) {
                throw new Exception("Tipo de archivo no permitido");
            }

            // Validar tamaño (5MB máximo)
            if ($archivo['size'] > 5 * 1024 * 1024) {
                throw new Exception("El archivo es demasiado grande");
            }

            // Generar nombre único
            $nombreArchivo = ($id_evento ? "evento_" . $id_evento : uniqid()) . "." . $extension;
            $rutaCompleta = $directorio . $nombreArchivo;

            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                return "assets/img/eventos/" . $nombreArchivo;
            }

            throw new Exception("Error al guardar la imagen");

        } catch (Exception $e) {
            return false;
        }
    }

    // ... (continuará con más métodos)
}
?> 