<?php
class BalnearioController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene los detalles del balneario
     */
    public function obtenerBalneario($id_balneario) {
        $query = "SELECT * FROM balnearios WHERE id_balneario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene los servicios asociados al balneario
     * @param int $id_balneario ID del balneario
     * @return array Lista de servicios
     */
    public function obtenerServicios($id_balneario) {
        $query = "SELECT s.* 
                 FROM servicios s
                 INNER JOIN detalles_servicios ds ON s.id_servicio = ds.id_servicio
                 WHERE ds.id_balneario = ?
                 ORDER BY s.nombre_servicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene las reservaciones del balneario
     * @param int $id_balneario ID del balneario
     * @return array Lista de reservaciones
     */
    public function obtenerReservaciones($id_balneario) {
        $query = "SELECT * 
                 FROM reservaciones 
                 WHERE id_balneario = ? 
                 AND fecha_reserva >= CURDATE()
                 ORDER BY fecha_reserva ASC, hora_reserva ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza los detalles del balneario
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
                        precio_general_adultos = ?,
                        precio_general_infantes = ?
                     WHERE id_balneario = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "sssssssssssddi",
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
                $datos['precio_general_adultos'],
                $datos['precio_general_infantes'],
                $id_balneario
            );

            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error en actualizarBalneario: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Valida los datos del balneario
     */
    public function validarDatos($datos) {
        $errores = [];

        // Validar campos requeridos
        $campos_requeridos = [
            'nombre_balneario' => 'Nombre',
            'descripcion_balneario' => 'Descripción',
            'direccion_balneario' => 'Dirección',
            'horario_apertura' => 'Horario de apertura',
            'horario_cierre' => 'Horario de cierre',
            'telefono_balneario' => 'Teléfono',
            'email_balneario' => 'Email',
            'precio_general_adultos' => 'Precio para adultos',
            'precio_general_infantes' => 'Precio para infantes'
        ];

        foreach ($campos_requeridos as $campo => $nombre) {
            if (empty($datos[$campo])) {
                $errores[] = "El campo {$nombre} es requerido";
            }
        }

        // Validar formato de email
        if (!empty($datos['email_balneario']) && !filter_var($datos['email_balneario'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El formato del email no es válido";
        }

        // Validar formato de teléfono (10 dígitos)
        if (!empty($datos['telefono_balneario']) && !preg_match('/^\d{10}$/', $datos['telefono_balneario'])) {
            $errores[] = "El teléfono debe tener 10 dígitos";
        }

        // Validar precios
        if (!empty($datos['precio_general_adultos']) && (!is_numeric($datos['precio_general_adultos']) || $datos['precio_general_adultos'] <= 0)) {
            $errores[] = "El precio para adultos debe ser un número positivo";
        }

        if (!empty($datos['precio_general_infantes']) && (!is_numeric($datos['precio_general_infantes']) || $datos['precio_general_infantes'] <= 0)) {
            $errores[] = "El precio para infantes debe ser un número positivo";
        }

        // Validar que el precio de infantes no sea mayor al de adultos
        if (!empty($datos['precio_general_adultos']) && !empty($datos['precio_general_infantes'])) {
            if ($datos['precio_general_infantes'] >= $datos['precio_general_adultos']) {
                $errores[] = "El precio para infantes no debe ser mayor o igual al precio para adultos";
            }
        }

        // Validar horarios
        if (!empty($datos['horario_apertura']) && !empty($datos['horario_cierre'])) {
            if ($datos['horario_cierre'] <= $datos['horario_apertura']) {
                $errores[] = "El horario de cierre debe ser posterior al horario de apertura";
            }
        }

        return $errores;
    }
}
?> 