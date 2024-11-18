<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../globalControllers/EmailController.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class BoletinController {
    private $conn;
    private $emailController;

    public function __construct($db) {
        $this->conn = $db;
        $this->emailController = new EmailController($db);
    }

    /**
     * Obtiene todos los boletines asociados a un balneario específico
     */
    public function obtenerBoletines($id_balneario) {
        try {
            $query = "SELECT b.*, u.nombre_usuario 
                     FROM boletines b 
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario 
                     WHERE u.id_balneario = ?
                     ORDER BY b.id_boletin DESC";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $id_balneario);
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en obtenerBoletines: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de los boletines y suscriptores
     */
    public function obtenerEstadisticas($id_balneario) {
        try {
            $stats = [
                'total_boletines' => 0,
                'borradores' => 0,
                'enviados' => 0,
                'total_suscriptores' => 0
            ];

            // Verificar que el id_balneario sea válido
            if (!$id_balneario || !is_numeric($id_balneario)) {
                throw new Exception("ID de balneario no válido");
            }

            // Contar boletines filtrando por balneario
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN b.fecha_envio_boletin IS NULL THEN 1 ELSE 0 END) as borradores,
                        SUM(CASE WHEN b.fecha_envio_boletin IS NOT NULL THEN 1 ELSE 0 END) as enviados
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE u.id_balneario = ?";
            
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Error en la preparación de la consulta de boletines");
            }

            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            $conteos = $result->fetch_assoc();

            $stats['total_boletines'] = $conteos['total'] ?? 0;
            $stats['borradores'] = $conteos['borradores'] ?? 0;
            $stats['enviados'] = $conteos['enviados'] ?? 0;

            $stmt->close();

            // Contar suscriptores
            $query_suscriptores = "SELECT COUNT(DISTINCT email_usuario_contacto) as total_suscriptores 
                                  FROM contactos 
                                  WHERE suscripcion_boletin = 1 
                                  AND email_usuario_contacto IS NOT NULL 
                                  AND email_usuario_contacto != ''";
            
            $stmt = $this->conn->prepare($query_suscriptores);
            if ($stmt === false) {
                throw new Exception("Error en la preparación de la consulta de suscriptores");
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $suscriptores = $result->fetch_assoc();
            $stats['total_suscriptores'] = $suscriptores['total_suscriptores'] ?? 0;

            $stmt->close();
            return $stats;

        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return [
                'total_boletines' => 0,
                'borradores' => 0,
                'enviados' => 0,
                'total_suscriptores' => 0
            ];
        }
    }

    /**
     * Obtiene los suscriptores que se han suscrito al boletín
     */
    public function obtenerSuscriptores($id_balneario) {
        try {
            // Debug
            error_log("Obteniendo suscriptores para balneario ID: " . $id_balneario);

            $query = "SELECT DISTINCT 
                        nombre_usuario_contacto,
                        email_usuario_contacto,
                        telefono_usuario_contacto
                     FROM contactos 
                     WHERE suscripcion_boletin = 1 
                     AND email_usuario_contacto IS NOT NULL
                     AND email_usuario_contacto != ''
                     ORDER BY nombre_usuario_contacto ASC";
            
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                error_log("Error preparando consulta: " . $this->conn->error);
                throw new Exception("Error al preparar la consulta de suscriptores");
            }

            if (!$stmt->execute()) {
                error_log("Error ejecutando consulta: " . $stmt->error);
                throw new Exception("Error al obtener suscriptores");
            }

            $result = $stmt->get_result();
            $suscriptores = $result->fetch_all(MYSQLI_ASSOC);

            // Debug
            error_log("Suscriptores encontrados: " . count($suscriptores));

            // Transformar resultados al formato esperado
            $suscriptores_formateados = array_map(function($suscriptor) {
                return [
                    'nombre_usuario' => $suscriptor['nombre_usuario_contacto'],
                    'email_usuario' => $suscriptor['email_usuario_contacto'],
                    'telefono_usuario' => $suscriptor['telefono_usuario_contacto']
                ];
            }, $suscriptores);

            $stmt->close();
            return $suscriptores_formateados;
            
        } catch (Exception $e) {
            error_log("Error en obtenerSuscriptores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un boletín específico verificando que pertenezca al balneario
     */
    public function obtenerBoletin($id_boletin, $id_balneario) {
        try {
            $query = "SELECT b.*, u.nombre_usuario 
                     FROM boletines b 
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario 
                     WHERE b.id_boletin = ? 
                     AND u.id_balneario = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("ii", $id_boletin, $id_balneario);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Error en obtenerBoletin: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo borrador de boletín
     */
    public function crearBorrador($titulo, $contenido, $id_usuario) {
        try {
            // Debug
            error_log("Iniciando crearBorrador");
            error_log("Datos recibidos - Título: $titulo, ID Usuario: $id_usuario");
            
            // Validar datos
            if (empty($titulo) || empty($contenido) || empty($id_usuario)) {
                throw new Exception("Todos los campos son requeridos");
            }

            // Consulta SQL simplificada solo con los campos necesarios
            $query = "INSERT INTO boletines (
                        titulo_boletin, 
                        contenido_boletin,
                        fecha_envio_boletin,
                        id_usuario
                    ) VALUES (?, ?, NULL, ?)";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("ssi", $titulo, $contenido, $id_usuario);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al crear el borrador: " . $stmt->error);
            }
            
            error_log("Borrador creado exitosamente con ID: " . $stmt->insert_id);
            return [
                'success' => true,
                'message' => 'Boletín guardado exitosamente como borrador',
                'id_boletin' => $stmt->insert_id
            ];
            
        } catch (Exception $e) {
            error_log("Error en crearBorrador: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Envía un boletín a los suscriptores
     */
    public function enviarBoletin($id_boletin, $id_balneario) {
        try {
            // Obtener información del boletín
            $boletin = $this->obtenerBoletin($id_boletin, $id_balneario);
            if (!$boletin) {
                throw new Exception('Boletín no encontrado');
            }

            // Obtener nombre del balneario
            $query = "SELECT nombre_balneario FROM balnearios WHERE id_balneario = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            $balneario = $result->fetch_assoc();
            $nombreBalneario = $balneario ? $balneario['nombre_balneario'] : 'Balneario';

            // Obtener suscriptores
            $suscriptores = $this->obtenerSuscriptores($id_balneario);
            if (empty($suscriptores)) {
                throw new Exception('No hay suscriptores para enviar el boletín');
            }

            $enviados = 0;
            $errores = [];

            // Enviar a cada suscriptor
            foreach ($suscriptores as $suscriptor) {
                try {
                    // Crear asunto del correo con el nombre del balneario
                    $asunto = "[{$nombreBalneario}] {$boletin['titulo_boletin']}";

                    // Preparar el contenido del correo
                    $contenido = "Estimado(a) {$suscriptor['nombre_usuario']},\n\n";
                    $contenido .= $boletin['contenido_boletin'];

                    $resultado = $this->emailController->enviarBoletinMasivo(
                        $asunto,
                        $contenido,
                        [$suscriptor],
                        $id_balneario
                    );

                    if ($resultado['success']) {
                        $enviados++;
                    } else {
                        $errores[] = [
                            'email' => $suscriptor['email_usuario'],
                            'error' => $resultado['message']
                        ];
                    }
                } catch (Exception $e) {
                    $errores[] = [
                        'email' => $suscriptor['email_usuario'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Actualizar estado del boletín si se envió al menos a un suscriptor
            if ($enviados > 0) {
                $this->actualizarEstado($id_boletin, 'enviado');
            }

            return [
                'success' => $enviados > 0,
                'message' => 'Boletín enviado exitosamente',
                'enviados' => $enviados,
                'total_suscriptores' => count($suscriptores),
                'errores' => $errores
            ];

        } catch (Exception $e) {
            error_log("Error en enviarBoletin: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'enviados' => 0,
                'errores' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Actualiza el estado de un boletín
     */
    private function actualizarEstado($id_boletin, $estado) {
        try {
            $fecha_envio = $estado === 'enviado' ? date('Y-m-d H:i:s') : null;
            $query = "UPDATE boletines 
                     SET fecha_envio_boletin = ? 
                     WHERE id_boletin = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $fecha_envio, $id_boletin);
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en actualizarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo boletín
     * @param string $titulo Título del boletín
     * @param string $contenido Contenido del boletín
     * @param int $id_usuario ID del usuario que crea el boletín
     * @param bool $es_borrador Si es true, se guarda como borrador
     * @return bool|string True si se creó correctamente, mensaje de error si falló
     */
    public function crearBoletin($titulo, $contenido, $id_usuario, $es_borrador = true) {
        try {
            $query = "INSERT INTO boletines (
                        titulo_boletin, 
                        contenido_boletin,
                        fecha_envio_boletin,
                        id_usuario
                    ) VALUES (?, ?, NULL, ?)";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("ssi", $titulo, $contenido, $id_usuario);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al crear el boletín: " . $stmt->error);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error en crearBoletin: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Actualiza un boletín existente
     */
    public function actualizarBoletin($id_boletin, $titulo, $contenido, $id_balneario) {
        try {
            // Verificar que el boletín existe y pertenece al balneario
            $boletin = $this->obtenerBoletin($id_boletin, $id_balneario);
            if (!$boletin) {
                throw new Exception("Boletín no encontrado");
            }

            // Verificar que no haya sido enviado
            if ($boletin['fecha_envio_boletin'] !== null) {
                throw new Exception("No se puede editar un boletín ya enviado");
            }

            $query = "UPDATE boletines 
                     SET titulo_boletin = ?, contenido_boletin = ? 
                     WHERE id_boletin = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta");
            }

            $stmt->bind_param("ssi", $titulo, $contenido, $id_boletin);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar el boletín");
            }

            return [
                'success' => true,
                'message' => 'Boletín actualizado exitosamente'
            ];
            
        } catch (Exception $e) {
            error_log("Error en actualizarBoletin: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un boletín
     */
    public function eliminarBoletin($id_boletin, $id_balneario) {
        try {
            // Verificar que el boletín existe y pertenece al balneario
            $boletin = $this->obtenerBoletin($id_boletin, $id_balneario);
            if (!$boletin) {
                throw new Exception("Boletín no encontrado");
            }

            // Verificar que no haya sido enviado
            if ($boletin['fecha_envio_boletin'] !== null) {
                throw new Exception("No se puede eliminar un boletín ya enviado");
            }

            $query = "DELETE FROM boletines WHERE id_boletin = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta");
            }

            $stmt->bind_param("i", $id_boletin);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar el boletín");
            }

            return [
                'success' => true,
                'message' => 'Boletín eliminado exitosamente'
            ];
            
        } catch (Exception $e) {
            error_log("Error en eliminarBoletin: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?> 