<?php
// classes/Auth.php

class Auth {
    private $db;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->user = new User();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function register(array $data): array {
        // Validações
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'Todos os campos são obrigatórios.'];
        }
        
        if (strlen($data['password']) < 6) {
            return ['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres.'];
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'E-mail inválido.'];
        }
        
        if ($this->user->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Este e-mail já está cadastrado.'];
        }
        
        if ($this->user->findByUsername($data['username'])) {
            return ['success' => false, 'message' => 'Este nome de usuário já está em uso.'];
        }
        
        // Criar usuário
        $userId = $this->user->create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'full_name' => $data['full_name'] ?? $data['username']
        ]);
        
        if ($userId) {
            // Auto login
            $this->createSession($userId);
            return ['success' => true, 'message' => 'Conta criada com sucesso!', 'user_id' => $userId];
        }
        
        return ['success' => false, 'message' => 'Erro ao criar conta. Tente novamente.'];
    }
    
    public function login(string $email, string $password, bool $remember = false): array {
        $user = $this->user->findByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'E-mail ou senha incorretos.'];
        }
        
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Sua conta está desativada.'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'E-mail ou senha incorretos.'];
        }
        
        $this->createSession($user['id']);
        
        // Atualizar streak
        $this->updateStreak($user['id']);
        
        if ($remember) {
            $this->createRememberToken($user['id']);
        }
        
        return [
            'success' => true, 
            'message' => 'Login realizado com sucesso!',
            'user' => $user
        ];
    }
    
    public function logout(): void {
        if (isset($_SESSION['user_id'])) {
            // Remover token de sessão do banco
            $this->db->delete('user_sessions', 'user_id = ?', [$_SESSION['user_id']]);
        }
        
        // Limpar cookie remember
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    public function isLoggedIn(): bool {
        if (isset($_SESSION['user_id'])) {
            return true;
        }
        
        // Verificar remember token
        if (isset($_COOKIE['remember_token'])) {
            return $this->loginWithRememberToken($_COOKIE['remember_token']);
        }
        
        return false;
    }
    
    public function getCurrentUser(): ?array {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->user->find($_SESSION['user_id']);
    }
    
    public function getCurrentUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function isAdmin(): bool {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'admin';
    }
    
    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
    }
    
    public function requireAdmin(): void {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            header('Location: ' . SITE_URL . '/user/');
            exit;
        }
    }
    
    private function createSession(int $userId): void {
        $_SESSION['user_id'] = $userId;
        $_SESSION['login_time'] = time();
        
        // Atualizar última atividade
        $this->user->updateLastActivity($userId);
    }
    
    private function createRememberToken(int $userId): void {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        $this->db->insert('user_sessions', [
            'user_id' => $userId,
            'session_token' => $hashedToken,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'expires_at' => $expiresAt
        ]);
        
        setcookie('remember_token', $token, time() + SESSION_LIFETIME, '/', '', false, true);
    }
    
    private function loginWithRememberToken(string $token): bool {
        $hashedToken = hash('sha256', $token);
        
        $session = $this->db->fetch(
            "SELECT user_id FROM user_sessions WHERE session_token = ? AND expires_at > NOW()",
            [$hashedToken]
        );
        
        if ($session) {
            $this->createSession($session['user_id']);
            return true;
        }
        
        return false;
    }
    
    private function updateStreak(int $userId): void {
        $user = $this->user->find($userId);
        $lastActivity = $user['last_activity'];
        $today = date('Y-m-d');
        
        if ($lastActivity === null) {
            $newStreak = 1;
        } elseif ($lastActivity === $today) {
            return; // Já atualizou hoje
        } elseif ($lastActivity === date('Y-m-d', strtotime('-1 day'))) {
            $newStreak = $user['streak_days'] + 1;
        } else {
            $newStreak = 1; // Streak quebrado
        }
        
        $this->user->update($userId, ['streak_days' => $newStreak]);
    }
}