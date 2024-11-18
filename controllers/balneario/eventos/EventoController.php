<?php
require_once 'EventoImageController.php';

/**
 * Controlador para la gestión de eventos
 * Maneja todas las operaciones CRUD relacionadas con eventos de los balnearios
 */
class EventoController {
    private $conn;
    private $imageController;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->imageController = new EventoImageController($db);
    }

    /**
     * Obtiene todos los eventos de un balneario específico
     * @param int $id_balneario ID del balneario
     * @return array Lista de eventos con todos sus detalles
     */
    public function obtenerEventos($id_balneario) {
        $query = "SELECT * FROM eventos 
                 WHERE id_balneario = ? 
                 ORDER BY fecha_inicio_evento DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $eventos = [];
        while ($row = $result->fetch_assoc()) {
            $eventos[] = $row;
        }
        
        return $eventos;
    }

    /**
     * Obtiene los detalles de un evento específico
     * @param int $id_evento ID del evento
     * @return array|null Detalles del evento o null si no existe
     */
    public function obtenerEvento($id_evento) {
        $query = "SELECT e.*, b.nombre_balneario 
                 FROM eventos e 
                 INNER JOIN balnearios b ON e.id_balneario = b.id_balneario 
                 WHERE e.id_evento = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_evento);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Crea un nuevo evento con imagen
     */
    public function crearEvento($datos, $imagen = null) {
        try {
            // Procesar imagen si se proporcionó una
            $rutaImagen = null;
            if ($imagen && $imagen['size'] > 0) {
                $resultadoImagen = $this->imageController->guardarImagen($imagen, $datos['id_balneario']);
                if (!$resultadoImagen['success']) {
                    throw new Exception($resultadoImagen['error']);
                }
                $rutaImagen = $resultadoImagen['path'];
            }

            // Insertar evento en la base de datos
            $query = "INSERT INTO eventos (
                        id_balneario, titulo_evento, descripcion_evento,
                        fecha_inicio_evento, fecha_fin_evento, url_imagen_evento
                    ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "isssss",
                $datos['id_balneario'],
                $datos['titulo'],
                $datos['descripcion'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $rutaImagen
            );

            if (!$stmt->execute()) {
                // Si falla la inserción, eliminar la imagen si se subió
                if ($rutaImagen) {
                    $this->imageController->eliminarImagen($rutaImagen);
                }
                throw new Exception("Error al crear el evento: " . $stmt->error);
            }

            return true;

        } catch (Exception $e) {
            error_log("Error en crearEvento: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Actualiza un evento existente, incluyendo su imagen
     */
    public function actualizarEvento($datos, $imagen = null) {
        try {
            $eventoActual = $this->obtenerEvento($datos['id_evento']);
            if (!$eventoActual) {
                throw new Exception("Evento no encontrado");
            }

            // Procesar nueva imagen si se proporcionó una
            $rutaImagen = $eventoActual['url_imagen_evento'];
            if ($imagen && $imagen['size'] > 0) {
                $resultadoImagen = $this->imageController->reemplazarImagen(
                    $imagen, 
                    $rutaImagen, 
                    $datos['id_balneario']
                );
                if (!$resultadoImagen['success']) {
                    throw new Exception($resultadoImagen['error']);
                }
                $rutaImagen = $resultadoImagen['path'];
            }

            // Actualizar evento en la base de datos
            $query = "UPDATE eventos SET 
                        titulo_evento = ?,
                        descripcion_evento = ?,
                        fecha_inicio_evento = ?,
                        fecha_fin_evento = ?,
                        url_imagen_evento = ?
                     WHERE id_evento = ? AND id_balneario = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "sssssii",
                $datos['titulo'],
                $datos['descripcion'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $rutaImagen,
                $datos['id_evento'],
                $datos['id_balneario']
            );

            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar el evento: " . $stmt->error);
            }

            return true;

        } catch (Exception $e) {
            error_log("Error en actualizarEvento: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Elimina un evento y su imagen asociada
     */
    public function eliminarEvento($id_evento, $id_balneario) {
        try {
            // Obtener información del evento para eliminar la imagen
            $evento = $this->obtenerEvento($id_evento);
            if (!$evento) {
                throw new Exception("Evento no encontrado");
            }

            // Eliminar el evento de la base de datos
            $query = "DELETE FROM eventos WHERE id_evento = ? AND id_balneario = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $id_evento, $id_balneario);

            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar el evento: " . $stmt->error);
            }

            // Si se eliminó el evento, eliminar la imagen asociada
            if ($evento['url_imagen_evento']) {
                $this->imageController->eliminarImagen($evento['url_imagen_evento']);
            }

            return true;

        } catch (Exception $e) {
            error_log("Error en eliminarEvento: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Verifica si un evento pertenece a un balneario específico
     * @param int $id_evento ID del evento
     * @param int $id_balneario ID del balneario
     * @return bool True si el evento pertenece al balneario, False si no
     */
    public function verificarPertenencia($id_evento, $id_balneario) {
        $query = "SELECT id_evento FROM eventos 
                 WHERE id_evento = ? AND id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_evento, $id_balneario);
        $stmt->execute();
        
        return $stmt->get_result()->num_rows > 0;
    }
}
?>