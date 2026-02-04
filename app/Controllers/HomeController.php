<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $stmt = $this->db->getPdo()->query("
            SELECT * FROM courses 
            WHERE is_published = 1 
            ORDER BY created_at DESC 
            LIMIT 6
        ");
        $courses = $stmt->fetchAll();
        
        $stmt = $this->db->getPdo()->query("
            SELECT * FROM news 
            WHERE is_published = 1 
            ORDER BY published_at DESC 
            LIMIT 3
        ");
        $news = $stmt->fetchAll();
        
        $stmt = $this->db->getPdo()->query("
            SELECT COUNT(*) as total FROM users WHERE role = 'student'
        ");
        $totalStudents = $stmt->fetch()['total'];
        
        $this->view('home/index', [
            'title' => 'GameDev Academy - Aprenda Desenvolvimento de Jogos',
            'courses' => $courses,
            'news' => $news,
            'totalStudents' => $totalStudents,
            'layout' => 'main'
        ]);
    }
    
    public function courses()
    {
        $stmt = $this->db->getPdo()->query("
            SELECT c.*, cat.name as category_name 
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.is_published = 1 
            ORDER BY c.created_at DESC
        ");
        $courses = $stmt->fetchAll();
        
        $this->view('home/courses', [
            'title' => 'Cursos',
            'courses' => $courses,
            'layout' => 'main'
        ]);
    }
    
    public function news()
    {
        $stmt = $this->db->getPdo()->query("
            SELECT * FROM news 
            WHERE is_published = 1 
            ORDER BY published_at DESC
        ");
        $news = $stmt->fetchAll();
        
        $this->view('home/news', [
            'title' => 'Notícias',
            'news' => $news,
            'layout' => 'main'
        ]);
    }
}