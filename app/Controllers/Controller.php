<?php
// app/Controllers/Controller.php

namespace App\Controllers;

class Controller
{
    protected $auth;
    protected $db;
    
    public function __construct()
    {
        $this->auth = new \Auth();
        $this->db = \Database::getInstance();
    }
    
    protected function view($view, $data = [])
    {
        // Adicionar dados globais
        $data['auth'] = $this->auth;
        $data['user'] = $this->auth->getUser();
        
        \View::render($view, $data);
    }
    
    protected function redirect($url)
    {
        header("Location: " . url($url));
        exit;
    }
    
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}