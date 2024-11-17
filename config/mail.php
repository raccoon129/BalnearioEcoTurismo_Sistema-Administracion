<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailConfig {
    private $mail;
    private $conn;
    private $isProduction = false;

    public function __construct($db) {
        $this->conn = $db;
        $this->configureMail();
    }

    /**
     * Configura la instancia de PHPMailer según el entorno
     */
    private function configureMail() {
        $this->mail = new PHPMailer(true);
        
        // Configuración base común
        $this->mail->isSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->setFrom('noreply@tubalneario.com', 'Balneario Eco Turismo');

        // Aplicar configuración según el entorno
        if ($this->isProduction) {
            $this->setProductionConfig();
        } else {
            $this->setTestingConfig();
        }
    }

    /**
     * Configura el servidor de producción
     */
    private function setProductionConfig() {
        $this->mail->Host = 'bulk.smtp.mailtrap.io';
        $this->mail->Port = 587;
        $this->mail->Username = 'apismtp@mailtrap.io';
        $this->mail->Password = '52413fba55e86193632fe6d381fc0397';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    /**
     * Configura el servidor de pruebas
     */
    private function setTestingConfig() {
        $this->mail->Host = 'sandbox.smtp.mailtrap.io';
        $this->mail->Port = 2525;
        $this->mail->Username = '675e589bd51f45';
        $this->mail->Password = '19a0228f814ddc';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    /**
     * Cambia el modo entre producción y pruebas
     * @param bool $isProd true para producción, false para pruebas
     */
    public function setProductionMode($isProd) {
        if ($this->isProduction !== $isProd) {
            $this->isProduction = $isProd;
            $this->configureMail();
        }
    }

    /**
     * Obtiene la instancia configurada de PHPMailer
     * @return PHPMailer
     */
    public function getMail() {
        return $this->mail;
    }
}
?> 