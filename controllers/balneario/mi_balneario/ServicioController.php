<?php
class ServicioController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene un servicio específico
     * @param int $id_servicio ID del servicio
     * @param int $id_balneario ID del balneario para verificar permisos
     * @return array|false Datos del servicio o false si no existe o no tiene permisos
     */
    public function obtenerServicio($id_servicio, $id_balneario) {
        try {
            $query = "SELECT s.* 
                     FROM servicios s
                     INNER JOIN detalles_servicios ds ON s.id_servicio = ds.id_servicio
                     WHERE s.id_servicio = ? AND ds.id_balneario = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $id_servicio, $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Guarda un nuevo servicio
     */
    public function guardarServicio($datos, $id_balneario) {
        try {
            $this->conn->begin_transaction();

            // Insertar el servicio
            $query = "INSERT INTO servicios (nombre_servicio, descripcion_servicio, precio_adicional) 
                     VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssd", 
                $datos['nombre_servicio'],
                $datos['descripcion_servicio'],
                $datos['precio_adicional']
            );
            $stmt->execute();
            $id_servicio = $stmt->insert_id;

            // Asociar el servicio al balneario
            $query = "INSERT INTO detalles_servicios (id_balneario, id_servicio) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $id_balneario, $id_servicio);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Actualiza un servicio existente
     */
    public function actualizarServicio($id_servicio, $datos, $id_balneario) {
        try {
            // Verificar que el servicio pertenece al balneario
            if (!$this->verificarPropietarioServicio($id_servicio, $id_balneario)) {
                throw new Exception("No tiene permisos para modificar este servicio");
            }

            $query = "UPDATE servicios 
                     SET nombre_servicio = ?, 
                         descripcion_servicio = ?, 
                         precio_adicional = ? 
                     WHERE id_servicio = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssdi", 
                $datos['nombre_servicio'],
                $datos['descripcion_servicio'],
                $datos['precio_adicional'],
                $id_servicio
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Elimina un servicio
     */
    public function eliminarServicio($id_servicio, $id_balneario) {
        try {
            if (!$this->verificarPropietarioServicio($id_servicio, $id_balneario)) {
                throw new Exception("No tiene permisos para eliminar este servicio");
            }

            $this->conn->begin_transaction();

            // Eliminar la relación en detalles_servicios
            $query = "DELETE FROM detalles_servicios WHERE id_servicio = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_servicio);
            $stmt->execute();

            // Eliminar el servicio
            $query = "DELETE FROM servicios WHERE id_servicio = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_servicio);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Verifica si un servicio pertenece a un balneario
     */
    private function verificarPropietarioServicio($id_servicio, $id_balneario) {
        $query = "SELECT 1 FROM detalles_servicios 
                 WHERE id_servicio = ? AND id_balneario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_servicio, $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
?> 