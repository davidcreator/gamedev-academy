<?php
/**
 * GameDev Academy - Classe de Email
 * Gerencia todos os envios de email do sistema
 */

class Mailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $encryption;
    private $from_email;
    private $from_name;
    
    /**
     * Construtor da classe
     */
    public function __construct() {
        // Carregar configurações (definidas em includes/config.php)
        $this->host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $this->port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->username = defined('SMTP_USER') ? SMTP_USER : '';
        $this->password = defined('SMTP_PASS') ? SMTP_PASS : '';
        $this->encryption = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
        $this->from_email = defined('MAIL_FROM') ? MAIL_FROM : 'noreply@gamedevacademy.com';
        $this->from_name = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'GameDev Academy';
    }
    
    /**
     * Enviar email de recuperação de senha
     */
    public function sendPasswordResetEmail($to_email, $user_name, $reset_token) {
        $reset_link = SITE_URL . '/reset-password.php?token=' . $reset_token;
        
        $subject = "🔐 Recuperação de Senha - GameDev Academy";
        
        $html_body = $this->getPasswordResetTemplate($user_name, $reset_link);
        $text_body = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_body));
        
        return $this->send($to_email, $subject, $html_body, $text_body);
    }
    
    /**
     * Template HTML para email de recuperação
     */
    private function getPasswordResetTemplate($name, $reset_link) {
        $template = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Recuperação de Senha</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
            <table cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="color: #ffffff; margin: 0; font-size: 28px;">🎮 GameDev Academy</h1>
                                    <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Recuperação de Senha</p>
                                </td>
                            </tr>
                            
                            <!-- Body -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <h2 style="color: #333333; margin: 0 0 20px 0; font-size: 20px;">
                                        Olá, ' . htmlspecialchars($name) . '!
                                    </h2>
                                    
                                    <p style="color: #666666; line-height: 1.6; margin: 0 0 20px 0;">
                                        Recebemos uma solicitação para redefinir a senha da sua conta na GameDev Academy.
                                    </p>
                                    
                                    <p style="color: #666666; line-height: 1.6; margin: 0 0 30px 0;">
                                        Se você fez esta solicitação, clique no botão abaixo para criar uma nova senha:
                                    </p>
                                    
                                    <!-- Button -->
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td align="center" style="padding: 20px 0;">
                                                <a href="' . htmlspecialchars($reset_link) . '" 
                                                   style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                                          color: #ffffff; 
                                                          padding: 14px 35px; 
                                                          text-decoration: none; 
                                                          border-radius: 50px; 
                                                          display: inline-block; 
                                                          font-weight: 600; 
                                                          font-size: 16px;">
                                                    Redefinir Minha Senha
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- Alternative Link -->
                                    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 30px 0;">
                                        <p style="color: #666666; font-size: 14px; margin: 0 0 10px 0;">
                                            Problemas com o botão? Copie e cole o link abaixo no seu navegador:
                                        </p>
                                        <p style="color: #667eea; font-size: 13px; word-break: break-all; margin: 0; background: white; padding: 10px; border: 1px solid #e0e0e0; border-radius: 3px;">
                                            ' . htmlspecialchars($reset_link) . '
                                        </p>
                                    </div>
                                    
                                    <!-- Warning -->
                                    <div style="background-color: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                        <p style="color: #856404; margin: 0; font-size: 14px;">
                                            <strong>⚠️ Importante:</strong> Este link é válido por apenas 1 hora por questões de segurança.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                                    <p style="color: #999999; font-size: 13px; margin: 0 0 10px 0;">
                                        <strong style="color: #666666;">Não solicitou esta alteração?</strong>
                                    </p>
                                    <p style="color: #999999; font-size: 13px; margin: 0 0 20px 0;">
                                        Ignore este email e sua senha permanecerá a mesma.
                                    </p>
                                    <p style="color: #999999; font-size: 12px; margin: 0;">
                                        © ' . date('Y') . ' GameDev Academy. Todos os direitos reservados.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Enviar email de senha alterada
     */
    public function sendPasswordChangedNotification($to_email, $user_name) {
        $subject = "✅ Senha Alterada - GameDev Academy";
        
        $html_body = $this->getPasswordChangedTemplate($user_name);
        $text_body = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_body));
        
        return $this->send($to_email, $subject, $html_body, $text_body);
    }
    
    /**
     * Template para notificação de senha alterada
     */
    private function getPasswordChangedTemplate($name) {
        $current_time = date('d/m/Y \à\s H:i');
        $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Desconhecido';
        
        $template = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Senha Alterada</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
            <table cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 10px; overflow: hidden;">
                            <!-- Header -->
                            <tr>
                                <td style="background-color: #28a745; padding: 30px; text-align: center;">
                                    <h1 style="color: #ffffff; margin: 0;">✅ Senha Alterada com Sucesso</h1>
                                </td>
                            </tr>
                            
                            <!-- Body -->
                            <tr>
                                <td style="padding: 30px;">
                                    <p style="color: #333333; font-size: 16px; margin: 0 0 20px 0;">
                                        Olá, ' . htmlspecialchars($name) . '!
                                    </p>
                                    
                                    <p style="color: #666666; line-height: 1.6;">
                                        Sua senha foi alterada com sucesso.
                                    </p>
                                    
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                        <p style="color: #333333; margin: 0 0 10px 0;"><strong>Detalhes da alteração:</strong></p>
                                        <p style="color: #666666; margin: 5px 0;">📅 Data/Hora: ' . $current_time . '</p>
                                        <p style="color: #666666; margin: 5px 0;">🌐 IP: ' . $ip_address . '</p>
                                    </div>
                                    
                                    <p style="color: #dc3545; font-size: 14px; margin: 20px 0;">
                                        <strong>Se você não realizou esta alteração, entre em contato conosco imediatamente.</strong>
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #f8f9fa; padding: 20px; text-align: center;">
                                    <p style="color: #999999; font-size: 12px; margin: 0;">
                                        © ' . date('Y') . ' GameDev Academy. Todos os direitos reservados.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Enviar email de boas-vindas
     */
    public function sendWelcomeEmail($to_email, $user_name) {
        $subject = "🎮 Bem-vindo ao GameDev Academy!";
        
        $html_body = $this->getWelcomeTemplate($user_name);
        $text_body = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_body));
        
        return $this->send($to_email, $subject, $html_body, $text_body);
    }
    
    /**
     * Template de boas-vindas
     */
    private function getWelcomeTemplate($name) {
        $login_link = SITE_URL . '/login.php';
        
        $template = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Bem-vindo</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
            <table cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 10px;">
                            <tr>
                                <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; text-align: center;">
                                    <h1 style="color: #ffffff; margin: 0;">🎮 Bem-vindo ao GameDev Academy!</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 30px;">
                                    <h2 style="color: #333333; margin: 0 0 20px 0;">Olá, ' . htmlspecialchars($name) . '!</h2>
                                    
                                    <p style="color: #666666; line-height: 1.6;">
                                        Sua conta foi criada com sucesso! Estamos muito felizes em ter você conosco.
                                    </p>
                                    
                                    <p style="color: #666666; line-height: 1.6;">
                                        A GameDev Academy é sua plataforma completa para aprender desenvolvimento de jogos.
                                    </p>
                                    
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td align="center" style="padding: 30px 0;">
                                                <a href="' . htmlspecialchars($login_link) . '" 
                                                   style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                                          color: #ffffff; 
                                                          padding: 14px 35px; 
                                                          text-decoration: none; 
                                                          border-radius: 50px; 
                                                          display: inline-block; 
                                                          font-weight: 600;">
                                                    Acessar Plataforma
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Método genérico para enviar emails
     */
    public function send($to, $subject, $html_body, $text_body = '') {
        // Verificar se PHPMailer está disponível
        if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                return $this->sendWithPHPMailer($to, $subject, $html_body, $text_body);
            }
        }
        
        // Fallback para função mail() do PHP
        return $this->sendWithMail($to, $subject, $html_body);
    }
    
    /**
     * Enviar usando PHPMailer
     */
    private function sendWithPHPMailer($to, $subject, $html_body, $text_body) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = $this->encryption;
            $mail->Port       = $this->port;
            $mail->CharSet    = 'UTF-8';
            
            // Desabilitar verificação SSL em desenvolvimento
            if ($_SERVER['SERVER_NAME'] == 'localhost') {
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }
            
            // Remetente e destinatário
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to);
            $mail->addReplyTo('support@gamedevacademy.com', 'Suporte');
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html_body;
            $mail->AltBody = $text_body ?: strip_tags($html_body);
            
            $mail->send();
            return true;
            
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log("Erro PHPMailer: " . $mail->ErrorInfo);
            return false;
        } catch (\Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar usando função mail() nativa
     */
    private function sendWithMail($to, $subject, $html_body) {
        // Headers para email HTML
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: support@gamedevacademy.com',
            'X-Mailer: PHP/' . phpversion()
        );
        
        // Enviar email
        $success = @mail($to, $subject, $html_body, implode("\r\n", $headers));
        
        if (!$success) {
            error_log("Erro ao enviar email para: " . $to);
        }
        
        return $success;
    }
    
    /**
     * Testar configuração de email
     */
    public function testEmailConfiguration($test_email) {
        $subject = "🧪 Teste de Configuração - GameDev Academy";
        
        $html_body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
        </head>
        <body style="font-family: Arial, sans-serif; padding: 20px;">
            <h2>Teste de Email</h2>
            <p>Se você está recebendo este email, a configuração está funcionando corretamente!</p>
            <p>Data/Hora: ' . date('d/m/Y H:i:s') . '</p>
            <p>Método: ' . (class_exists('PHPMailer\\PHPMailer\\PHPMailer') ? 'PHPMailer' : 'mail() nativo') . '</p>
        </body>
        </html>';
        
        return $this->send($test_email, $subject, $html_body);
    }
}

// Fim do arquivo - não adicione código após esta linha
?>