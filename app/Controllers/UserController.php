<?php
// app/Controllers/UserController.php

namespace App\Controllers;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->auth->requireLogin();
    }
    
    public function dashboard()
    {
        $userId = $this->auth->getUser()['id'];
        
        // Estatísticas do usuário
        $stats = [];
        
        // XP e nível
        $user = $this->auth->getUser();
        $stats['xp'] = $user['xp_total'];
        $stats['level'] = $user['level'];
        $stats['coins'] = $user['coins'];
        $stats['streak'] = $user['streak_days'];
        
        // Cursos
        $stmt = $this->db->getPdo()->prepare("
            SELECT COUNT(*) as total 
            FROM enrollments 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $stats['courses_enrolled'] = $stmt->fetch()['total'];
        
        // Cursos em andamento
        $stmt = $this->db->getPdo()->prepare("
            SELECT c.*, e.progress_percentage 
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.user_id = ?
            ORDER BY e.last_accessed DESC
            LIMIT 3
        ");
        $stmt->execute([$userId]);
        $courses = $stmt->fetchAll();
        
        $this->view('user/dashboard', [
            'title' => 'Meu Dashboard',
            'stats' => $stats,
            'courses' => $courses,
            'layout' => 'user'
        ]);
    }
    
    public function courses()
    {
        $userId = $this->auth->getUser()['id'];
        
        $stmt = $this->db->getPdo()->prepare("
            SELECT c.*, e.progress_percentage, cat.name as category_name
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE e.user_id = ?
            ORDER BY e.last_accessed DESC
        ");
        $stmt->execute([$userId]);
        $courses = $stmt->fetchAll();
        
        $this->view('user/courses', [
            'title' => 'Meus Cursos',
            'courses' => $courses,
            'layout' => 'user'
        ]);
    }
    
    public function profile()
    {
        $user = $this->auth->getUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Atualizar perfil
            $stmt = $this->db->getPdo()->prepare("
                UPDATE users 
                SET full_name = ?, bio = ?, github_url = ?, linkedin_url = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['full_name'],
                $_POST['bio'] ?? '',
                $_POST['github_url'] ?? '',
                $_POST['linkedin_url'] ?? '',
                $user['id']
            ]);
            
            $success = 'Perfil atualizado com sucesso!';
        }
        
        $this->view('user/profile', [
            'title' => 'Meu Perfil',
            'user' => $user,
            'success' => $success ?? null,
            'layout' => 'user'
        ]);
    }
    
    public function achievements()
    {
        $userId = $this->auth->getUser()['id'];
        
        // Por enquanto, conquistas mockadas
        $achievements = [
            ['name' => 'Primeiro Passo', 'description' => 'Complete sua primeira lição', 'icon' => '🎯', 'unlocked' => true],
            ['name' => 'Estudante Dedicado', 'description' => 'Complete 10 lições', 'icon' => '📖', 'unlocked' => false],
            ['name' => 'Maratonista', 'description' => 'Complete 50 lições', 'icon' => '🏃', 'unlocked' => false],
        ];
        
        $this->view('user/achievements', [
            'title' => 'Conquistas',
            'achievements' => $achievements,
            'layout' => 'user'
        ]);
    }
}