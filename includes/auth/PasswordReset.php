<?php
/**
 * GameDev Academy - Password Reset Manager
 * Gerencia recuperação e alteração de senhas
 * 
 * @version 1.0
 */

namespace GameDev\Auth;

class PasswordReset {
    
    private $pdo;
    private $prefix;
    private $tokenExpiry = 3600; // 1 hora em segundos
    private $errors = [];
    
    /**
     * Construtor
     */
    public function __construct(\PDO $pdo, $prefix = '') {
        $this->pdo = $pdo;
        $this->prefix = $prefix;
    }
    
    /**
     * Definir tempo de expiração do token
     */
    public function setTokenExpiry($seconds) {
        $this->tokenExpiry = $seconds;
    }
    
    /**
     * Solicitar recuperação de senha
     */
    public function requestReset($email) {
        $this->errors = [];
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Email inválido';
            return false;
        }
        
        // Buscar usuário
        $user = $this->findUserByEmail($email);
        
        if (!$user) {
            // Não revelar se o email existe ou não (segurança)
            // Retornar true mas não enviar email
            return [
                'success' => true,
                'message' => 'Se o email estiver cadastrado, você receberá as instruções.',
                'user' => null
            ];
        }
        
        // Gerar token
        $token = $this->generateToken();
        $hashedToken = hash('sha256', $token);
        
        // Salvar token no banco
        $saved = $this->saveResetToken($user['id'], $hashedToken);
        
        if (!$saved) {
            $this->errors[] = 'Erro ao gerar token de recuperação';
            return false;
        }
        
        return [
            'success' => true,
            'message' => 'Token gerado com sucesso',
            'user' => $user,
            'token' => $token, // Token não-hasheado para o link
            'expires_at' => date('Y-m-d H:i:s', time() + $this->tokenExpiry)
        ];
    }
    
    /**
     * Verificar se token é válido
     */
    public function verifyToken($token) {
        $this->errors = [];
        
        if (empty($token)) {
            $this->errors[] = 'Token não fornecido';
            return false;
        }
        
        $hashedToken = hash('sha256', $token);
        $table = $this->prefix . 'password_resets';
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT pr.*, u.email, u.username, u.name 
                FROM `{$table}` pr
                JOIN `{$this->prefix}users` u ON pr.user_id = u.id OR pr.email = u.email
                WHERE pr.token = :token 
                AND pr.used = 0 
                AND pr.expires_at > NOW()
                ORDER BY pr.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([':token' => $hashedToken]);
            $reset = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$reset) {
                $this->errors[] = 'Token inválido ou expirado';
                return false;
            }
            
            return [
                'valid' => true,
                'user_id' => $reset['user_id'],
                'email' => $reset['email'],
                'username' => $reset['username'] ?? '',
                'name' => $reset['name'] ?? ''
            ];
            
        } catch (\Exception $e) {
            $this->errors[] = 'Erro ao verificar token: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Redefinir senha
     */
    public function resetPassword($token, $newPassword, $confirmPassword) {
        $this->errors = [];
        
        // Validar senhas
        if ($newPassword !== $confirmPassword) {
            $this->errors[] = 'As senhas não coincidem';
            return false;
        }
        
        // Validar força da senha
        $passwordValidation = $this->validatePasswordStrength($newPassword);
        if (!$passwordValidation['valid']) {
            $this->errors = array_merge($this->errors, $passwordValidation['errors']);
            return false;
        }
        
        // Verificar token
        $tokenData = $this->verifyToken($token);
        if (!$tokenData) {
            return false;
        }
        
        // Atualizar senha
        try {
            $this->pdo->beginTransaction();
            
            // Hash da nova senha
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Atualizar usuário
            $usersTable = $this->prefix . 'users';
            $stmt = $this->pdo->prepare("
                UPDATE `{$usersTable}` 
                SET password = :password, updated_at = NOW() 
                WHERE id = :user_id OR email = :email
            ");
            $stmt->execute([
                ':password' => $hashedPassword,
                ':user_id' => $tokenData['user_id'],
                ':email' => $tokenData['email']
            ]);
            
            // Marcar token como usado
            $this->invalidateToken($token);
            
            // Invalidar todos os outros tokens do usuário
            $this->invalidateAllUserTokens($tokenData['email']);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Senha alterada com sucesso',
                'email' => $tokenData['email']
            ];
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            $this->errors[] = 'Erro ao atualizar senha: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Alterar senha (usuário logado)
     */
    public function changePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        $this->errors = [];
        
        // Validar senhas
        if ($newPassword !== $confirmPassword) {
            $this->errors[] = 'As senhas não coincidem';
            return false;
        }
        
        // Validar força da senha
        $passwordValidation = $this->validatePasswordStrength($newPassword);
        if (!$passwordValidation['valid']) {
            $this->errors = array_merge($this->errors, $passwordValidation['errors']);
            return false;
        }
        
        // Buscar usuário
        $user = $this->findUserById($userId);
        if (!$user) {
            $this->errors[] = 'Usuário não encontrado';
            return false;
        }
        
        // Verificar senha atual
        if (!password_verify($currentPassword, $user['password'])) {
            $this->errors[] = 'Senha atual incorreta';
            return false;
        }
        
        // Atualizar senha
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $table = $this->prefix . 'users';
            $stmt = $this->pdo->prepare("
                UPDATE `{$table}` 
                SET password = :password, updated_at = NOW() 
                WHERE id = :user_id
            ");
            $stmt->execute([
                ':password' => $hashedPassword,
                ':user_id' => $userId
            ]);
            
            return [
                'success' => true,
                'message' => 'Senha alterada com sucesso'
            ];
            
        } catch (\Exception $e) {
            $this->errors[] = 'Erro ao atualizar senha: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Validar força da senha
     */
    public function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'A senha deve ter no mínimo 8 caracteres';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'A senha deve ter pelo menos uma letra maiúscula';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'A senha deve ter pelo menos uma letra minúscula';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'A senha deve ter pelo menos um número';
        }
        
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) {
            $errors[] = 'A senha deve ter pelo menos um caractere especial (!@#$%^&*)';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculateStrength($password)
        ];
    }
    
    /**
     * Calcular força da senha
     */
    private function calculateStrength($password) {
        $score = 0;
        
        if (strlen($password) >= 8) $score++;
        if (strlen($password) >= 12) $score++;
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) $score++;
        
        if ($score <= 2) return 'weak';
        if ($score <= 4) return 'medium';
        if ($score <= 5) return 'strong';
        return 'excellent';
    }
    
    /**
     * Gerar token seguro
     */
    private function generateToken($length = 64) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Salvar token de recuperação
     */
    private function saveResetToken($userId, $hashedToken) {
        $table = $this->prefix . 'password_resets';
        
        try {
            // Verificar se a tabela existe
            $check = $this->pdo->query("SHOW TABLES LIKE '{$table}'");
            
            if ($check->rowCount() === 0) {
                // Criar tabela se não existir
                $this->createPasswordResetsTable();
            }
            
            // Buscar email do usuário
            $user = $this->findUserById($userId);
            $email = $user ? $user['email'] : '';
            
            // Inserir token
            $stmt = $this->pdo->prepare("
                INSERT INTO `{$table}` (user_id, email, token, expires_at, created_at) 
                VALUES (:user_id, :email, :token, :expires_at, NOW())
            ");
            
            return $stmt->execute([
                ':user_id' => $userId,
                ':email' => $email,
                ':token' => $hashedToken,
                ':expires_at' => date('Y-m-d H:i:s', time() + $this->tokenExpiry)
            ]);
            
        } catch (\Exception $e) {
            $this->errors[] = 'Erro ao salvar token: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Criar tabela password_resets se não existir
     */
    private function createPasswordResetsTable() {
        $table = $this->prefix . 'password_resets';
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NULL,
            `email` VARCHAR(255) NOT NULL,
            `token` VARCHAR(255) NOT NULL,
            `used` TINYINT(1) DEFAULT 0,
            `expires_at` DATETIME NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_token` (`token`),
            INDEX `idx_email` (`email`),
            INDEX `idx_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql);
    }
    
    /**
     * Invalidar token
     */
    private function invalidateToken($token) {
        $hashedToken = hash('sha256', $token);
        $table = $this->prefix . 'password_resets';
        
        $stmt = $this->pdo->prepare("UPDATE `{$table}` SET used = 1 WHERE token = :token");
        return $stmt->execute([':token' => $hashedToken]);
    }
    
    /**
     * Invalidar todos os tokens do usuário
     */
    private function invalidateAllUserTokens($email) {
        $table = $this->prefix . 'password_resets';
        
        $stmt = $this->pdo->prepare("UPDATE `{$table}` SET used = 1 WHERE email = :email");
        return $stmt->execute([':email' => $email]);
    }
    
    /**
     * Limpar tokens expirados
     */
    public function cleanExpiredTokens() {
        $table = $this->prefix . 'password_resets';
        
        try {
            $stmt = $this->pdo->prepare("DELETE FROM `{$table}` WHERE expires_at < NOW() OR used = 1");
            return $stmt->execute();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Buscar usuário por email
     */
    private function findUserByEmail($email) {
        $table = $this->prefix . 'users';
        
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `{$table}` WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Buscar usuário por ID
     */
    private function findUserById($id) {
        $table = $this->prefix . 'users';
        
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `{$table}` WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return null;
        }
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
}