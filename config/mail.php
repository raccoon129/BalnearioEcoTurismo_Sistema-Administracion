<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailConfig {
    private $mail;
    private $conn;
    private $isProduction = true; // Variable para controlar el entorno

    public function __construct($db) {
        $this->conn = $db;
        $this->mail = new PHPMailer(true);

        // Configuración base común
        $this->mail->isSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->setFrom('noreply@tubalneario.com', 'Balneario Eco Turismo');

        // Seleccionar configuración según el entorno
        if ($this->isProduction) {
            // Configuración de producción
            $this->mail->Host = 'bulk.smtp.mailtrap.io';
            $this->mail->Port = 587;
            $this->mail->Username = 'apismtp@mailtrap.io';
            $this->mail->Password = '52413fba55e86193632fe6d381fc0397';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            // Configuración de pruebas
            $this->mail->Host = 'sandbox.smtp.mailtrap.io';
            $this->mail->Port = 2525;
            $this->mail->Username = '675e589bd51f45';
            $this->mail->Password = '19a0228f814ddc';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
    }

    /**
     * Cambia el entorno entre producción y pruebas
     * @param bool $isProd true para producción, false para pruebas
     */
    public function setProductionMode($isProd) {
        $this->isProduction = $isProd;
        
        // Reconfigurar las credenciales según el nuevo modo
        if ($this->isProduction) {
            $this->mail->Host = 'bulk.smtp.mailtrap.io';
            $this->mail->Port = 587;
            $this->mail->Username = 'apismtp@mailtrap.io';
            $this->mail->Password = '52413fba55e86193632fe6d381fc0397';
        } else {
            $this->mail->Host = 'sandbox.smtp.mailtrap.io';
            $this->mail->Port = 2525;
            $this->mail->Username = '675e589bd51f45';
            $this->mail->Password = '19a0228f814ddc';
        }
    }

    /**
     * Envía un boletín a todos los suscriptores de un balneario específico
     * @param int $id_balneario ID del balneario
     * @param string $titulo Título del boletín
     * @param string $contenido Contenido del boletín
     * @return array Resultado del envío con detalles
     */
    public function enviarBoletin($id_balneario, $titulo, $contenido) {
        try {
            // Obtener lista de suscriptores
            $query = "SELECT DISTINCT email_usuario, nombre_usuario 
                     FROM opiniones_usuarios 
                     WHERE id_balneario = ? 
                     AND suscripcion_boletin = 1 
                     AND email_usuario IS NOT NULL";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $enviados = 0;
            $errores = [];

            // Obtener información del balneario
            $queryBalneario = "SELECT nombre_balneario FROM balnearios WHERE id_balneario = ?";
            $stmtBalneario = $this->conn->prepare($queryBalneario);
            $stmtBalneario->bind_param("i", $id_balneario);
            $stmtBalneario->execute();
            $balneario = $stmtBalneario->get_result()->fetch_assoc();

            while ($suscriptor = $result->fetch_assoc()) {
                try {
                    $this->mail->clearAddresses();
                    $this->mail->addAddress($suscriptor['email_usuario'], $suscriptor['nombre_usuario']);
                    $this->mail->Subject = $titulo;
                    
                    // Crear plantilla HTML para el correo
                    $mensaje = $this->crearPlantillaHTML(
                        $balneario['nombre_balneario'],
                        $titulo,
                        $contenido,
                        $suscriptor['nombre_usuario']
                    );
                    
                    $this->mail->isHTML(true);
                    $this->mail->Body = $mensaje;
                    $this->mail->AltBody = strip_tags($contenido);
                    
                    $this->mail->send();
                    $enviados++;
                    
                } catch (Exception $e) {
                    $errores[] = [
                        'email' => $suscriptor['email_usuario'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return [
                'success' => true,
                'enviados' => $enviados,
                'total_suscriptores' => $result->num_rows,
                'errores' => $errores
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Crea una plantilla HTML para el correo
     */
    private function crearPlantillaHTML($nombreBalneario, $titulo, $contenido, $nombreSuscriptor) {
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
                    <h1>' . htmlspecialchars($nombreBalneario) . '</h1>
                </div>
                <div class="content">
                    <p>Hola ' . htmlspecialchars($nombreSuscriptor) . ',</p>
                    <h2>' . htmlspecialchars($titulo) . '</h2>
                    ' . nl2br(htmlspecialchars($contenido)) . '
                </div>
                <div class="footer">
                    <p>Este correo fue enviado porque te suscribiste a nuestro boletín.</p>
                    <p>Si no deseas recibir más correos, puedes cancelar tu suscripción en tu próxima visita al balneario.</p>
                </div>
            </div>
        </body>
        </html>';
    }
}
?> 