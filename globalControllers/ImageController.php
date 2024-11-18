<?php
class ImageController {
    private $anchoMaximo = 1920;  // Ancho máximo HD
    private $altoMaximo = 1080; // Alto máximo HD
    private $calidad = 80;     // Calidad de compresión
    private $tamañoMaximoArchivo = 2097152; // 2MB en bytes
    private $tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg'];
    private $rutaSubida;

    public function __construct($rutaSubida = 'uploads/') {
        $this->rutaSubida = rtrim($rutaSubida, '/') . '/';
        if (!is_dir($this->rutaSubida)) {
            mkdir($this->rutaSubida, 0777, true);
        }
    }

    /**
     * Procesa y guarda una imagen
     * @param array $archivo Archivo de imagen ($_FILES['input_name'])
     * @param string $subcarpeta Subcarpeta donde se guardará la imagen
     * @return array Resultado del procesamiento con la ruta de la imagen o error
     */
    public function procesarYGuardar($archivo, $subcarpeta = '') {
        try {
            // Validaciones iniciales
            if (!isset($archivo['tmp_name']) || empty($archivo['tmp_name'])) {
                throw new Exception('No se ha subido ningún archivo');
            }

            // Validar tipo de archivo
            if (!in_array($archivo['type'], $this->tiposPermitidos)) {
                throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPG y PNG');
            }

            // Validar tamaño
            if ($archivo['size'] > $this->tamañoMaximoArchivo) {
                throw new Exception('El archivo es demasiado grande. Máximo 2MB');
            }

            // Crear subcarpeta si no existe
            $rutaObjetivo = $this->rutaSubida . trim($subcarpeta, '/');
            if (!empty($subcarpeta) && !is_dir($rutaObjetivo)) {
                mkdir($rutaObjetivo, 0777, true);
            }

            // Obtener información de la imagen
            $infoImagen = getimagesize($archivo['tmp_name']);
            if ($infoImagen === false) {
                throw new Exception('Archivo no válido');
            }

            // Crear imagen desde el archivo original
            $imagenFuente = $this->crearImagenDesdeArchivo($archivo['tmp_name'], $archivo['type']);
            if (!$imagenFuente) {
                throw new Exception('Error al procesar la imagen');
            }

            // Calcular nuevas dimensiones manteniendo proporción
            $anchoOriginal = imagesx($imagenFuente);
            $altoOriginal = imagesy($imagenFuente);
            list($nuevoAncho, $nuevoAlto) = $this->calcularDimensiones($anchoOriginal, $altoOriginal);

            // Crear nueva imagen con las dimensiones calculadas
            $nuevaImagen = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

            // Mantener transparencia para PNG
            if ($archivo['type'] === 'image/png') {
                imagealphablending($nuevaImagen, false);
                imagesavealpha($nuevaImagen, true);
            }

            // Redimensionar
            imagecopyresampled(
                $nuevaImagen, $imagenFuente,
                0, 0, 0, 0,
                $nuevoAncho, $nuevoAlto,
                $anchoOriginal, $altoOriginal
            );

            // Generar nombre único
            $nombreArchivo = uniqid() . $this->obtenerExtensionPorTipo($archivo['type']);
            $rutaCompleta = $rutaObjetivo . '/' . $nombreArchivo;

            // Guardar imagen procesada
            $guardado = $this->guardarImagen($nuevaImagen, $rutaCompleta, $archivo['type']);
            if (!$guardado) {
                throw new Exception('Error al guardar la imagen');
            }

            // Liberar memoria
            imagedestroy($imagenFuente);
            imagedestroy($nuevaImagen);

            // Devolver información de la imagen procesada
            return [
                'success' => true,
                'path' => $subcarpeta . '/' . $nombreArchivo,
                'full_path' => $rutaCompleta,
                'width' => $nuevoAncho,
                'height' => $nuevoAlto,
                'size' => filesize($rutaCompleta)
            ];

        } catch (Exception $e) {
            error_log("Error en ImageController::procesarYGuardar: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Elimina una imagen
     * @param string $ruta Ruta de la imagen a eliminar
     * @return bool Resultado de la eliminación
     */
    public function eliminarImagen($ruta) {
        try {
            $rutaCompleta = $this->rutaSubida . ltrim($ruta, '/');
            if (file_exists($rutaCompleta)) {
                return unlink($rutaCompleta);
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en ImageController::eliminarImagen: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcula las nuevas dimensiones manteniendo la proporción
     */
    private function calcularDimensiones($ancho, $alto) {
        $ratio = $ancho / $alto;

        if ($ancho > $this->anchoMaximo) {
            $ancho = $this->anchoMaximo;
            $alto = $ancho / $ratio;
        }

        if ($alto > $this->altoMaximo) {
            $alto = $this->altoMaximo;
            $ancho = $alto * $ratio;
        }

        return [round($ancho), round($alto)];
    }

    /**
     * Crea una imagen desde el archivo original
     */
    private function crearImagenDesdeArchivo($rutaArchivo, $tipo) {
        switch ($tipo) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($rutaArchivo);
            case 'image/png':
                return imagecreatefrompng($rutaArchivo);
            default:
                return false;
        }
    }

    /**
     * Guarda la imagen procesada
     */
    private function guardarImagen($imagen, $ruta, $tipo) {
        switch ($tipo) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagejpeg($imagen, $ruta, $this->calidad);
            case 'image/png':
                return imagepng($imagen, $ruta, round($this->calidad / 10));
            default:
                return false;
        }
    }

    /**
     * Obtiene la extensión según el tipo MIME
     */
    private function obtenerExtensionPorTipo($tipo) {
        switch ($tipo) {
            case 'image/jpeg':
            case 'image/jpg':
                return '.jpg';
            case 'image/png':
                return '.png';
            default:
                return '';
        }
    }

    /**
     * Establece la calidad de compresión
     */
    public function establecerCalidad($calidad) {
        $this->calidad = max(0, min(100, $calidad));
        return $this;
    }

    /**
     * Establece las dimensiones máximas
     */
    public function establecerDimensionesMaximas($ancho, $alto) {
        $this->anchoMaximo = $ancho;
        $this->altoMaximo = $alto;
        return $this;
    }

    /**
     * Establece el tamaño máximo de archivo
     */
    public function establecerTamañoMaximoArchivo($tamaño) {
        $this->tamañoMaximoArchivo = $tamaño;
        return $this;
    }
}
?> 