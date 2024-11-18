<?php
require_once __DIR__ . '/../../../globalControllers/ImageController.php';

class EventoImageController {
    private $imageController;
    private $uploadPath;
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        $this->uploadPath = dirname(__FILE__) . '/../../../uploads/';
        $this->imageController = new ImageController($this->uploadPath);
    }

    /**
     * Procesa y guarda una imagen de evento
     */
    public function guardarImagen($archivo, $id_balneario) {
        try {
            // Primero obtenemos el nombre del balneario
            $query = "SELECT nombre_balneario FROM balnearios WHERE id_balneario = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            $balneario = $result->fetch_assoc();

            if (!$balneario) {
                throw new Exception('No se encontró el balneario');
            }

            // Crear nombre de carpeta seguro (sin espacios ni caracteres especiales)
            $nombreCarpeta = preg_replace('/[^a-zA-Z0-9]/', '_', $balneario['nombre_balneario']);
            $nombreCarpeta = strtolower($nombreCarpeta);

            // Definir la ruta relativa
            $rutaRelativa = $nombreCarpeta . '/eventos';

            // Procesar y guardar la imagen
            $resultado = $this->imageController->procesarYGuardar($archivo, $rutaRelativa);

            if (!$resultado['success']) {
                throw new Exception($resultado['error']);
            }

            // Devolver la ruta relativa para guardar en la base de datos
            // La ruta se guardará como: uploads/nombre_balneario/eventos/imagen.jpg
            return [
                'success' => true,
                'path' => 'uploads/' . $rutaRelativa . '/' . basename($resultado['path']),
                'full_path' => $resultado['full_path']
            ];

        } catch (Exception $e) {
            error_log("Error en guardarImagen: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Elimina una imagen existente
     */
    public function eliminarImagen($rutaImagen) {
        try {
            if (empty($rutaImagen)) {
                return true;
            }

            // Construir la ruta completa desde la raíz del proyecto
            $rutaCompleta = dirname(__FILE__) . '/../../../' . $rutaImagen;
            
            // Verificar si el archivo existe antes de intentar eliminarlo
            if (file_exists($rutaCompleta)) {
                if (!unlink($rutaCompleta)) {
                    throw new Exception("No se pudo eliminar la imagen");
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Error en eliminarImagen: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reemplaza una imagen existente por una nueva
     */
    public function reemplazarImagen($archivoNuevo, $rutaImagenAntigua, $id_balneario) {
        try {
            // Primero intentamos guardar la nueva imagen
            $resultadoGuardado = $this->guardarImagen($archivoNuevo, $id_balneario);
            
            if (!$resultadoGuardado['success']) {
                throw new Exception($resultadoGuardado['error']);
            }

            // Si se guardó correctamente la nueva, eliminamos la antigua
            if (!empty($rutaImagenAntigua)) {
                $this->eliminarImagen($rutaImagenAntigua);
            }

            return $resultadoGuardado;

        } catch (Exception $e) {
            error_log("Error en reemplazarImagen: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valida una imagen antes de procesarla
     */
    public function validarImagen($archivo) {
        try {
            if (!isset($archivo['tmp_name']) || empty($archivo['tmp_name'])) {
                throw new Exception('No se ha subido ningún archivo');
            }

            // Validar tipo de archivo
            $tipoPermitidos = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($archivo['type'], $tipoPermitidos)) {
                throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPG y PNG');
            }

            // Validar tamaño (2MB máximo)
            if ($archivo['size'] > 2 * 1024 * 1024) {
                throw new Exception('El archivo es demasiado grande. Máximo 2MB');
            }

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
?> 