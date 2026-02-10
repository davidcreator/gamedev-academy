<?php
/**
 * GameDev Academy - Email Mailer Class
 * Classe para envio de emails via SMTP ou mail()
 * 
 * @version 1.0
 */

namespace GameDev\Mail;

class Mailer {
    
    private $config = [];
    private $errors = [];
    private $debug = false;
    
    // Templates padrão
    private $templates = [];
    
    /**
     * Construtor
     */
    public function __construct($config = []) {
        $this->config = array_merge([
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_user' => '',
            'smtp_pass' => '',
            'smtp_security' => 'tls', // tls, ssl, none
            'from_email' => 'noreply@localhost',
            'from_name' => 'GameDev Academy',
            'charset' => 'UTF-8',
            'debug' => false
        ], $config);
        
        $this->debug = $this->config['debug'];
        $this->loadTemplates();
    }
    
    /**
     * Carregar configurações do banco de dados
     */
    public static function fromDatabase($pdo, $prefix = '') {
        $config = [];
        
        try {
            $table = $prefix . 'settings';
            $stmt = $pdo->query("SELECT `key`, `value` FROM `{$table}` WHERE `group` = 'email' OR `key` LIKE 'smtp_%'");
            
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $config[$row['key']] = $row['value'];
            }
        } catch (\Exception $e) {
            // Usar configurações padrão
        }
        
        return new self($config);
    }
    
    /**
     * Carregar templates de email
     */
    private function loadTemplates() {
        $this->templates = [
            'password_reset' => [
                'subject' => 'Recuperação de Senha - {{site_name}}',
                'html' => $this->getPasswordResetTemplate(),
                'text' => $this->getPasswordResetTextTemplate()
            ],
            'welcome' => [
                'subject' => 'Bem-vindo ao {{site_name}}!',
                'html' => $this->getWelcomeTemplate(),
                'text' => 'Bem-vindo ao {{site_name}}!'
            ],
            'email_verification' => [
                'subject' => 'Verifique seu email - {{site_name}}',
                'html' => $this->getEmailVerificationTemplate(),
                'text' => 'Verifique seu email acessando: {{verification_link}}'
            ]
        ];
    }
    
    /**
     * Enviar email
     */
    public function send($to, $subject, $body, $isHtml = true, $attachments = []) {
        $this->errors = [];
        
        // Validar email de destino
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Email de destino inválido';
            return false;
        }
        
        // Se SMTP configurado, usar SMTP
        if (!empty($this->config['smtp_host'])) {
            return $this->sendViaSMTP($to, $subject, $body, $isHtml, $attachments);
        }
        
        // Fallback para mail() nativo
        return $this->sendViaMail($to, $subject, $body, $isHtml);
    }
    
    /**
     * Enviar usando template
     */
    public function sendTemplate($to, $templateName, $variables = []) {
        if (!isset($this->templates[$templateName])) {
            $this->errors[] = "Template '{$templateName}' não encontrado";
            return false;
        }
        
        $template = $this->templates[$templateName];
        
        // Adicionar variáveis padrão
        $variables = array_merge([
            'site_name' => $this->config['from_name'] ?? 'GameDev Academy',
            'site_url' => $this->config['site_url'] ?? '',
            'current_year' => date('Y')
        ], $variables);
        
        // Substituir variáveis
        $subject = $this->replaceVariables($template['subject'], $variables);
        $body = $this->replaceVariables($template['html'], $variables);
        
        return $this->send($to, $subject, $body, true);
    }
    
    /**
     * Enviar email de recuperação de senha
     */
    public function sendPasswordReset($to, $resetLink, $userName = '', $expiresIn = '1 hora') {
        return $this->sendTemplate($to, 'password_reset', [
            'user_name' => $userName ?: 'Usuário',
            'reset_link' => $resetLink,
            'expires_in' => $expiresIn,
            'user_email' => $to
        ]);
    }
    
    /**
     * Enviar email de boas-vindas
     */
    public function sendWelcome($to, $userName, $loginLink = '') {
        return $this->sendTemplate($to, 'welcome', [
            'user_name' => $userName,
            'login_link' => $loginLink,
            'user_email' => $to
        ]);
    }
    
    /**
     * Enviar email de verificação
     */
    public function sendEmailVerification($to, $verificationLink, $userName = '') {
        return $this->sendTemplate($to, 'email_verification', [
            'user_name' => $userName ?: 'Usuário',
            'verification_link' => $verificationLink,
            'user_email' => $to
        ]);
    }
    
    /**
     * Enviar via SMTP
     */
    private function sendViaSMTP($to, $subject, $body, $isHtml = true, $attachments = []) {
        try {
            // Verificar se PHPMailer está disponível
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendViaPhpMailer($to, $subject, $body, $isHtml, $attachments);
            }
            
            // SMTP manual
            return $this->sendViaSMTPManual($to, $subject, $body, $isHtml);
            
        } catch (\Exception $e) {
            $this->errors[] = 'Erro SMTP: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Enviar via PHPMailer (se disponível)
     */
    private function sendViaPhpMailer($to, $subject, $body, $isHtml, $attachments) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Configurações SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->Port = $this->config['smtp_port'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_user'];
            $mail->Password = $this->config['smtp_pass'];
            
            // Segurança
            if ($this->config['smtp_security'] === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($this->config['smtp_security'] === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            }
            
            // Remetente
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addReplyTo($this->config['from_email'], $this->config['from_name']);
            
            // Destinatário
            $mail->addAddress($to);
            
            // Conteúdo
            $mail->isHTML($isHtml);
            $mail->CharSet = $this->config['charset'];
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            if ($isHtml) {
                $mail->AltBody = strip_tags($body);
            }
            
            // Anexos
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                } else {
                    $mail->addAttachment($attachment);
                }
            }
            
            return $mail->send();
            
        } catch (\Exception $e) {
            $this->errors[] = $mail->ErrorInfo;
            return false;
        }
    }
    
    /**
     * Enviar SMTP manual (sem PHPMailer)
     */
    private function sendViaSMTPManual($to, $subject, $body, $isHtml) {
        $host = $this->config['smtp_host'];
        $port = $this->config['smtp_port'];
        $user = $this->config['smtp_user'];
        $pass = $this->config['smtp_pass'];
        $security = $this->config['smtp_security'];
        
        // Criar conexão
        $socket = null;
        
        if ($security === 'ssl') {
            $socket = @fsockopen('ssl://' . $host, $port, $errno, $errstr, 30);
        } else {
            $socket = @fsockopen($host, $port, $errno, $errstr, 30);
        }
        
        if (!$socket) {
            $this->errors[] = "Não foi possível conectar ao servidor SMTP: {$errstr}";
            return false;
        }
        
        // Ler resposta inicial
        $this->smtpRead($socket);
        
        // EHLO
        $this->smtpWrite($socket, "EHLO " . gethostname());
        $this->smtpRead($socket);
        
        // STARTTLS se necessário
        if ($security === 'tls') {
            $this->smtpWrite($socket, "STARTTLS");
            $this->smtpRead($socket);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            $this->smtpWrite($socket, "EHLO " . gethostname());
            $this->smtpRead($socket);
        }
        
        // AUTH LOGIN
        $this->smtpWrite($socket, "AUTH LOGIN");
        $this->smtpRead($socket);
        
        $this->smtpWrite($socket, base64_encode($user));
        $this->smtpRead($socket);
        
        $this->smtpWrite($socket, base64_encode($pass));
        $response = $this->smtpRead($socket);
        
        if (strpos($response, '235') === false) {
            $this->errors[] = "Autenticação SMTP falhou";
            fclose($socket);
            return false;
        }
        
        // MAIL FROM
        $this->smtpWrite($socket, "MAIL FROM:<{$this->config['from_email']}>");
        $this->smtpRead($socket);
        
        // RCPT TO
        $this->smtpWrite($socket, "RCPT TO:<{$to}>");
        $this->smtpRead($socket);
        
        // DATA
        $this->smtpWrite($socket, "DATA");
        $this->smtpRead($socket);
        
        // Headers e body
        $headers = [];
        $headers[] = "From: {$this->config['from_name']} <{$this->config['from_email']}>";
        $headers[] = "To: {$to}";
        $headers[] = "Subject: {$subject}";
        $headers[] = "MIME-Version: 1.0";
        
        if ($isHtml) {
            $headers[] = "Content-Type: text/html; charset={$this->config['charset']}";
        } else {
            $headers[] = "Content-Type: text/plain; charset={$this->config['charset']}";
        }
        
        $headers[] = "Date: " . date('r');
        $headers[] = "";
        $headers[] = $body;
        $headers[] = ".";
        
        $this->smtpWrite($socket, implode("\r\n", $headers));
        $response = $this->smtpRead($socket);
        
        // QUIT
        $this->smtpWrite($socket, "QUIT");
        fclose($socket);
        
        return strpos($response, '250') !== false;
    }
    
    /**
     * Escrever no socket SMTP
     */
    private function smtpWrite($socket, $data) {
        fwrite($socket, $data . "\r\n");
        if ($this->debug) {
            echo "SEND: {$data}\n";
        }
    }
    
    /**
     * Ler do socket SMTP
     */
    private function smtpRead($socket) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        if ($this->debug) {
            echo "RECV: {$response}\n";
        }
        return $response;
    }
    
    /**
     * Enviar via mail() nativo
     */
    private function sendViaMail($to, $subject, $body, $isHtml = true) {
        $headers = [];
        $headers[] = "From: {$this->config['from_name']} <{$this->config['from_email']}>";
        $headers[] = "Reply-To: {$this->config['from_email']}";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        $headers[] = "MIME-Version: 1.0";
        
        if ($isHtml) {
            $headers[] = "Content-Type: text/html; charset={$this->config['charset']}";
        } else {
            $headers[] = "Content-Type: text/plain; charset={$this->config['charset']}";
        }
        
        $result = @mail($to, $subject, $body, implode("\r\n", $headers));
        
        if (!$result) {
            $this->errors[] = 'Falha ao enviar email via mail()';
        }
        
        return $result;
    }
    
    /**
     * Substituir variáveis no template
     */
    private function replaceVariables($text, $variables) {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }
    
    /**
     * Obter erros
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Obter último erro
     */
    public function getLastError() {
        return end($this->errors) ?: '';
    }
    
    /**
     * Template de recuperação de senha
     */
    private function getPasswordResetTemplate() {
        return '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
</head>
<body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">🔐 Recuperação de Senha</h1>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="font-size: 16px; color: #333; margin: 0 0 20px;">
                                Olá <strong>{{user_name}}</strong>,
                            </p>
                            
                            <p style="font-size: 16px; color: #555; margin: 0 0 20px; line-height: 1.6;">
                                Recebemos uma solicitação para redefinir a senha da sua conta associada ao email <strong>{{user_email}}</strong>.
                            </p>
                            
                            <p style="font-size: 16px; color: #555; margin: 0 0 30px; line-height: 1.6;">
                                Clique no botão abaixo para criar uma nova senha:
                            </p>
                            
                            <!-- Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td align="center">
                                        <a href="{{reset_link}}" 
                                           style="display: inline-block; 
                                                  background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%); 
                                                  color: #ffffff; 
                                                  padding: 15px 40px; 
                                                  text-decoration: none; 
                                                  border-radius: 8px; 
                                                  font-size: 16px; 
                                                  font-weight: bold;
                                                  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);">
                                            Redefinir Minha Senha
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 14px; color: #888; margin: 30px 0 20px; line-height: 1.6;">
                                Este link expira em <strong>{{expires_in}}</strong>.
                            </p>
                            
                            <p style="font-size: 14px; color: #888; margin: 0 0 20px; line-height: 1.6;">
                                Se você não solicitou esta alteração, ignore este email. Sua senha permanecerá a mesma.
                            </p>
                            
                            <!-- Alternative Link -->
                            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
                                <p style="font-size: 12px; color: #666; margin: 0 0 10px;">
                                    Se o botão não funcionar, copie e cole o link abaixo no seu navegador:
                                </p>
                                <p style="font-size: 12px; color: #2563eb; margin: 0; word-break: break-all;">
                                    {{reset_link}}
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-radius: 0 0 10px 10px;">
                            <p style="font-size: 12px; color: #888; margin: 0;">
                                © {{current_year}} {{site_name}}. Todos os direitos reservados.
                            </p>
                            <p style="font-size: 12px; color: #888; margin: 10px 0 0;">
                                Este é um email automático. Por favor, não responda.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
    
    /**
     * Template texto de recuperação de senha
     */
    private function getPasswordResetTextTemplate() {
        return "
Recuperação de Senha - {{site_name}}

Olá {{user_name}},

Recebemos uma solicitação para redefinir a senha da sua conta associada ao email {{user_email}}.

Para criar uma nova senha, acesse o link abaixo:
{{reset_link}}

Este link expira em {{expires_in}}.

Se você não solicitou esta alteração, ignore este email.

---
© {{current_year}} {{site_name}}
";
    }
    
    /**
     * Template de boas-vindas
     */
    private function getWelcomeTemplate() {
        return '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Bem-vindo!</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f7fa;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; background-color: #ffffff; border-radius: 10px;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                            <h1 style="color: #ffffff; margin: 0;">🎉 Bem-vindo!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="font-size: 16px; color: #333;">Olá <strong>{{user_name}}</strong>,</p>
                            <p style="font-size: 16px; color: #555; line-height: 1.6;">
                                Sua conta foi criada com sucesso no <strong>{{site_name}}</strong>!
                            </p>
                            <p style="font-size: 16px; color: #555; line-height: 1.6;">
                                Agora você pode acessar todas as funcionalidades do sistema.
                            </p>
                            <table role="presentation" style="width: 100%;">
                                <tr>
                                    <td align="center" style="padding: 30px 0;">
                                        <a href="{{login_link}}" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                                            Acessar Minha Conta
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px;">
                            <p style="font-size: 12px; color: #888; margin: 0;">© {{current_year}} {{site_name}}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
    
    /**
     * Template de verificação de email
     */
    private function getEmailVerificationTemplate() {
        return '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Verifique seu Email</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f7fa;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; background-color: #ffffff; border-radius: 10px;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                            <h1 style="color: #ffffff; margin: 0;">✉️ Verifique seu Email</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="font-size: 16px; color: #333;">Olá <strong>{{user_name}}</strong>,</p>
                            <p style="font-size: 16px; color: #555; line-height: 1.6;">
                                Clique no botão abaixo para verificar seu endereço de email:
                            </p>
                            <table role="presentation" style="width: 100%;">
                                <tr>
                                    <td align="center" style="padding: 30px 0;">
                                        <a href="{{verification_link}}" style="background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%); color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                                            Verificar Email
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px;">
                            <p style="font-size: 12px; color: #888; margin: 0;">© {{current_year}} {{site_name}}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}