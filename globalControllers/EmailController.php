<?php
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailController {
    private $mailConfig;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->mailConfig = new MailConfig($db);
    }

    /**
     * Envía un boletín a una lista de destinatarios
     */
    public function enviarBoletinMasivo($titulo, $contenido, $destinatarios, $id_balneario = null) {
        try {
            error_log("Iniciando envío de boletín masivo");
            error_log("Total destinatarios: " . count($destinatarios));
            
            $enviados = 0;
            $errores = [];
            
            // Validaciones previas
            if (empty($titulo)) {
                throw new Exception("El título del boletín no puede estar vacío");
            }
            
            if (empty($contenido)) {
                throw new Exception("El contenido del boletín no puede estar vacío");
            }

            if (empty($destinatarios)) {
                throw new Exception("No hay destinatarios para enviar el boletín");
            }

            // Validar estructura de destinatarios
            foreach ($destinatarios as $destinatario) {
                if (!isset($destinatario['email_usuario']) || !isset($destinatario['nombre_usuario'])) {
                    throw new Exception("Formato de destinatario inválido");
                }
            }

            // Si hay id_balneario, obtener su nombre
            $nombreBalneario = null;
            if ($id_balneario) {
                $nombreBalneario = $this->obtenerNombreBalneario($id_balneario);
                if (!$nombreBalneario) {
                    throw new Exception("No se pudo obtener la información del balneario");
                }
            }

            foreach ($destinatarios as $destinatario) {
                try {
                    if (empty($destinatario['email_usuario'])) {
                        throw new Exception("Email de destinatario vacío");
                    }

                    $mail = $this->mailConfig->getMail();
                    $mail->clearAddresses();
                    $mail->addAddress($destinatario['email_usuario'], $destinatario['nombre_usuario']);
                    $mail->Subject = $titulo;
                    
                    $mensaje = $this->crearPlantillaHTML(
                        $nombreBalneario,
                        $titulo,
                        $contenido,
                        $destinatario['nombre_usuario']
                    );
                    
                    $mail->isHTML(true);
                    $mail->Body = $mensaje;
                    $mail->AltBody = strip_tags($contenido);
                    
                    $mail->send();
                    $enviados++;
                    
                } catch (Exception $e) {
                    error_log("Error enviando correo a {$destinatario['email_usuario']}: " . $e->getMessage());
                    $errores[] = [
                        'email' => $destinatario['email_usuario'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            if ($enviados === 0 && count($errores) > 0) {
                throw new Exception("No se pudo enviar ningún correo. Revise los errores específicos.");
            }

            return [
                'success' => true,
                'enviados' => $enviados,
                'total_destinatarios' => count($destinatarios),
                'errores' => $errores
            ];

        } catch (Exception $e) {
            error_log("Error detallado en enviarBoletinMasivo: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'detalles' => [
                    'enviados' => $enviados ?? 0,
                    'total_destinatarios' => count($destinatarios),
                    'errores' => $errores,
                    'trace' => $e->getTraceAsString()
                ]
            ];
        }
    }

    /**
     * Crea una plantilla HTML para el correo
     */
    private function crearPlantillaHTML($nombreBalneario, $titulo, $contenido, $nombreDestinatario) {
        $encabezado = $nombreBalneario ? $nombreBalneario : 'Sistema de Balnearios';
        
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
                    <h1>' . htmlspecialchars($encabezado) . '</h1>
                </div>
                <div class="content">
                    <p>Hola ' . htmlspecialchars($nombreDestinatario) . ',</p>
                    <h2>' . htmlspecialchars($titulo) . '</h2>
                    ' . nl2br(htmlspecialchars($contenido)) . '
                </div>
                <div class="footer">
                    <p>Este correo fue enviado porque estás registrado en nuestro sistema.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Obtiene el nombre de un balneario
     */
    private function obtenerNombreBalneario($id_balneario) {
        try {
            $query = "SELECT nombre_balneario FROM balnearios WHERE id_balneario = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_balneario);
            $stmt->execute();
            $result = $stmt->get_result();
            $balneario = $result->fetch_assoc();
            return $balneario ? $balneario['nombre_balneario'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
