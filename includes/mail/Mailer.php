<?php
// Método atualizado para usar CSS externo ou classes definidas

public function getPasswordResetTemplate($name, $reset_link) {
    // Carregar CSS como string para inline (necessário para emails)
    $css = file_get_contents(__DIR__ . '/../../assets/css/email.css');
    
    return '
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>' . $css . '</style>
    </head>
    <body>
        <div class="email-wrapper">
            <div class="email-container">
                <div class="email-header">
                    <h1>🎮 GameDev Academy</h1>
                    <p>Recuperação de Senha</p>
                </div>
                
                <div class="email-body">
                    <div class="email-greeting">
                        Olá, ' . htmlspecialchars($name) . '!
                    </div>
                    
                    <div class="email-message">
                        <p>Recebemos uma solicitação para redefinir a senha da sua conta.</p>
                        <p>Se você fez esta solicitação, clique no botão abaixo:</p>
                    </div>
                    
                    <div class="email-button-container">
                        <a href="' . htmlspecialchars($reset_link) . '" class="email-button">
                            Redefinir Minha Senha
                        </a>
                    </div>
                    
                    <div class="email-link-alternative">
                        <p>Problemas com o botão? Copie e cole o link abaixo:</p>
                        <span class="email-link-url">' . htmlspecialchars($reset_link) . '</span>
                    </div>
                    
                    <div class="email-warning">
                        ⚠️ <strong>Importante:</strong> Este link expira em 1 hora.
                    </div>
                </div>
                
                <div class="email-footer">
                    <div class="email-footer-info">
                        <p><strong>Não solicitou esta alteração?</strong></p>
                        <p>Ignore este email e sua senha permanecerá a mesma.</p>
                        <br>
                        <p>© ' . date('Y') . ' GameDev Academy. Todos os direitos reservados.</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
}