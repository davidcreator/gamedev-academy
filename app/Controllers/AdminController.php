<?php
// app/Controllers/AdminController.php

namespace App\Controllers;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->auth->requireAdmin();
    }
    
    public function dashboard()
    {
        // Estatísticas
        $stats = [];
        
        $stmt = $this->db->getPdo()->query("SELECT COUNT(*) as total FROM users");
        $stats['users'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->getPdo()->query("SELECT COUNT(*) as total FROM courses");
        $stats['courses'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->getPdo()->query("SELECT COUNT(*) as total FROM news");
        $stats['news'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->getPdo()->query("SELECT SUM(xp_total) as total FROM users");
        $stats['xp_total'] = $stmt->fetch()['total'] ?? 0;
        
        // Últimos usuários
        $stmt = $this->db->getPdo()->query("
            SELECT * FROM users 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $latestUsers = $stmt->fetchAll();
        
        $this->view('admin/dashboard', [
            'title' => 'Dashboard Admin',
            'stats' => $stats,
            'latestUsers' => $latestUsers,
            'layout' => 'admin'
        ]);
    }
    
    public function users()
    {
        $stmt = $this->db->getPdo()->query("
            SELECT * FROM users 
            ORDER BY created_at DESC
        ");
        $users = $stmt->fetchAll();
        
        $this->view('admin/users', [
            'title' => 'Gerenciar Usuários',
            'users' => $users,
            'layout' => 'admin'
        ]);
    }
    
    public function courses()
    {
        $stmt = $this->db->getPdo()->query("
            SELECT c.*, cat.name as category_name 
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.id
            ORDER BY c.created_at DESC
        ");
        $courses = $stmt->fetchAll();
        
        $this->view('admin/courses', [
            'title' => 'Gerenciar Cursos',
            'courses' => $courses,
            'layout' => 'admin'
        ]);
    }
    
    public function news()
    {
        $stmt = $this->db->getPdo()->query("
            SELECT * FROM news 
            ORDER BY created_at DESC
        ");
        $news = $stmt->fetchAll();
        
        $this->view('admin/news', [
            'title' => 'Gerenciar Notícias',
            'news' => $news,
            'layout' => 'admin'
        ]);
    }
}