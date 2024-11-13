<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class BoletinController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los boletines asociados a un balneario específico
     * @param int $id_balneario ID del balneario
     * @return array Lista de boletines
     */
    public function obtenerBoletines($id_balneario) {
        $query = "SELECT b.*, u.nombre_usuario 
                 FROM boletines b 
                 INNER JOIN usuarios u ON b.id_usuario = u.id_usuario 
                 WHERE u.id_balneario = ?
                 ORDER BY b.fecha_envio_boletin DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_balneario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crea un nuevo boletín
     * @param string $titulo Título del boletín
     * @param string $contenido Contenido del boletín
     * @param int $id_usuario ID del usuario que crea el boletín
     * @param bool $es_borrador Indica si el boletín es un borrador
     * @return bool|string True si se creó correctamente, mensaje de error en caso contrario
     */
    public function crearBoletin($titulo, $contenido, $id_usuario, $es_borrador = true) {
        try {
            $query = "INSERT INTO boletines (titulo_boletin, contenido_boletin, fecha_envio_boletin, id_usuario) 
                     VALUES (?, ?, ?, ?)";
            
            // Si es borrador, la fecha_envio_boletin será NULL
            $fecha_envio = $es_borrador ? null : date('Y-m-d H:i:s');
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssi", $titulo, $contenido, $fecha_envio, $id_usuario);
            
            if ($stmt->execute()) {
                return true;
            }
            
            throw new Exception("Error al crear el boletín: " . $stmt->error);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Obtiene un boletín específico
     * @param int $id_boletin ID del boletín
     * @param int $id_balneario ID del balneario para verificar permisos
     * @return array|bool Datos del boletín o false si no existe o no tiene permisos
     */
    public function obtenerBoletin($id_boletin, $id_balneario) {
        $query = "SELECT b.*, u.nombre_usuario 
                 FROM boletines b 
                 INNER JOIN usuarios u ON b.id_usuario = u.id_usuario 
                 WHERE b.id_boletin = ? AND u.id_balneario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_boletin, $id_balneario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Envía un boletín a los suscriptores y actualiza su estado
     * @param int $id_boletin ID del boletín a enviar
     * @param int $id_balneario ID del balneario para verificar permisos
     * @return array Resultado del envío con detalles
     */
    public function enviarBoletin($id_boletin, $id_balneario) {
        try {
            // Obtener información del boletín
            $boletin = $this->obtenerBoletin($id_boletin, $id_balneario);
            if (!$boletin) {
                throw new Exception('Boletín no encontrado');
            }

            // Configurar PHPMailer
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Port = 2525;
            $mail->Username = '675e589bd51f45';
            $mail->Password = '19a0228f814ddc';
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('noreply@balnearios.com', 'Sistema de Boletines');

            // Obtener suscriptores del balneario
            $query = "SELECT DISTINCT email_usuario, nombre_usuario 
                     FROM opiniones_usuarios 
                     WHERE id_balneario = ? 
                     AND suscripcion_boletin = 1 
                     AND email_usuario IS NOT NULL";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $suscriptores = $stmt->get_result();

            $enviados = 0;
            $errores = [];

            // Enviar a cada suscriptor
            while ($suscriptor = $suscriptores->fetch_assoc()) {
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($suscriptor['email_usuario'], $suscriptor['nombre_usuario']);
                    $mail->Subject = $boletin['titulo_boletin'];
                    
                    // Crear contenido HTML
                    $contenidoHTML = $this->crearPlantillaEmail(
                        $boletin['titulo_boletin'],
                        $boletin['contenido_boletin'],
                        $suscriptor['nombre_usuario']
                    );
                    
                    $mail->isHTML(true);
                    $mail->Body = $contenidoHTML;
                    $mail->AltBody = strip_tags($boletin['contenido_boletin']);
                    
                    $mail->send();
                    $enviados++;
                } catch (Exception $e) {
                    $errores[] = [
                        'email' => $suscriptor['email_usuario'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Si se envió al menos a un suscriptor, actualizar la fecha de envío
            if ($enviados > 0) {
                $fecha_envio = date('Y-m-d H:i:s');
                $query = "UPDATE boletines SET fecha_envio_boletin = ? WHERE id_boletin = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("si", $fecha_envio, $id_boletin);
                $stmt->execute();
            }

            return [
                'success' => $enviados > 0,
                'enviados' => $enviados,
                'total_suscriptores' => $suscriptores->num_rows,
                'errores' => $errores
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'enviados' => 0,
                'errores' => [$e->getMessage()]
            ];
        }
    }

    private function crearPlantillaEmail($titulo, $contenido, $nombreSuscriptor) {
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
                    <p>Hola ' . htmlspecialchars($nombreSuscriptor) . ',</p>
                    ' . nl2br(htmlspecialchars($contenido)) . '
                </div>
                <div class="footer">
                    <p>Este correo fue enviado porque te suscribiste a nuestro boletín.</p>
                    <p>Si no deseas recibir más correos, puedes cancelar tu suscripción en tu próxima visita.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Elimina un boletín
     * @param int $id_boletin ID del boletín a eliminar
     * @return bool|string True si se eliminó correctamente, mensaje de error en caso contrario
     */
    public function eliminarBoletin($id_boletin) {
        $query = "DELETE FROM boletines WHERE id_boletin = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_boletin);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return "Error al eliminar el boletín: " . $stmt->error;
    }
}
?> 