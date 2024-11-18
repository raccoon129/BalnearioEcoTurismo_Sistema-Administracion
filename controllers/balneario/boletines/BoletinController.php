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
                     ORDER BY COALESCE(b.fecha_envio_boletin, b.fecha_creacion_boletin) DESC";
            
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
     * Obtiene estadísticas de los boletines
     */
    public function obtenerEstadisticas($id_balneario) {
        try {
            $stats = [
                'total_boletines' => 0,
                'borradores' => 0,
                'enviados' => 0,
                'total_suscriptores' => 0
            ];

            // Contar boletines
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN fecha_envio_boletin IS NULL THEN 1 ELSE 0 END) as borradores,
                        SUM(CASE WHEN fecha_envio_boletin IS NOT NULL THEN 1 ELSE 0 END) as enviados
                     FROM boletines b
                     INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                     WHERE u.id_balneario = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            $conteos = $result->fetch_assoc();

            $stats['total_boletines'] = $conteos['total'] ?? 0;
            $stats['borradores'] = $conteos['borradores'] ?? 0;
            $stats['enviados'] = $conteos['enviados'] ?? 0;

            // Contar suscriptores
            $query = "SELECT COUNT(DISTINCT email_usuario_contacto) as total_suscriptores 
                     FROM contactos 
                     WHERE suscripcion_boletin = 1";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_suscriptores'] = $result->fetch_assoc()['total_suscriptores'] ?? 0;

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
     * Obtiene los suscriptores
     */
    public function obtenerSuscriptores($id_balneario) {
        try {
            $query = "SELECT DISTINCT 
                        nombre_usuario_contacto as nombre_usuario,
                        email_usuario_contacto as email_usuario,
                        telefono_usuario_contacto as telefono_usuario
                     FROM contactos 
                     WHERE suscripcion_boletin = 1";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en obtenerSuscriptores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un boletín específico
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

            // Obtener suscriptores
            $suscriptores = $this->obtenerSuscriptores($id_balneario);
            if (empty($suscriptores)) {
                throw new Exception('No hay suscriptores para enviar el boletín');
            }

            $enviados = 0;
            $errores = [];

            // Preparar contenido del correo
            $contenidoHTML = $this->crearPlantillaEmail(
                $boletin['titulo_boletin'],
                $boletin['contenido_boletin']
            );

            // Enviar a cada suscriptor
            foreach ($suscriptores as $suscriptor) {
                try {
                    $resultado = $this->emailController->enviarBoletinMasivo(
                        $boletin['titulo_boletin'],
                        $contenidoHTML,
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
     * Crea la plantilla HTML para el correo
     */
    private function crearPlantillaEmail($titulo, $contenido) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . htmlspecialchars($titulo) . '</h1>
                </div>
                <div class="content">
                    ' . nl2br(htmlspecialchars($contenido)) . '
                </div>
                <div class="footer">
                    <p>Este correo fue enviado porque estás suscrito a nuestro boletín.</p>
                    <p>Si no deseas recibir más correos, puedes cancelar tu suscripción en tu próxima visita.</p>
                </div>
            </div>
        </body>
        </html>';
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
}
?> 