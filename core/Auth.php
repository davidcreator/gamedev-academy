<?php
// core/Auth.php

class Auth
{
    private $db;
    private $user = null;
    
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = Database::getInstance();
        $this->checkUser();
    }
    
    protected function checkUser()
    {
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->getPdo()->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
            $stmt->execute([$_SESSION['user_id']]);
            $this->user = $stmt->fetch();
            
            if (!$this->user) {
                $this->logout();
            }
        }
    }
    
    public function login($email, $password)
    {
        $stmt = $this->db->getPdo()->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // Atualizar última atividade
            $stmt = $this->db->getPdo()->prepare("UPDATE users SET last_activity = CURDATE() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            $this->user = $user;
            return true;
        }
        
        return false;
    }
    
    public function register($data)
    {
        // Verificar se usuário já existe
        $stmt = $this->db->getPdo()->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$data['email'], $data['username']]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'E-mail ou usuário já cadastrado'];
        }
        
        // Inserir novo usuário
        $stmt = $this->db->getPdo()->prepare("
            INSERT INTO users (username, email, password, full_name, role) 
            VALUES (?, ?, ?, ?, 'student')
        ");
        
        $success = $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['full_name']
        ]);
        
        if ($success) {
            // Auto login
            $this->login($data['email'], $data['password']);
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Erro ao criar conta'];
    }
    
    public function logout()
    {
        session_destroy();
        $this->user = null;
    }
    
    public function isLoggedIn()
    {
        return $this->user !== null;
    }
    
    public function isAdmin()
    {
        return $this->user && $this->user['role'] === 'admin';
    }
    
    public function isStudent()
    {
        return $this->user && $this->user['role'] === 'student';
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header('Location: ' . url('login'));
            exit;
        }
    }
    
    public function requireAdmin()
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            header('Location: ' . url('user'));
            exit;
        }
    }
}