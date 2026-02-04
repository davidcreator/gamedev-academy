<?php
// app/Controllers/AuthController.php

namespace App\Controllers;

class AuthController extends Controller
{
    public function login()
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect($this->auth->isAdmin() ? 'admin' : 'user');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($this->auth->login($email, $password)) {
                $this->redirect($this->auth->isAdmin() ? 'admin' : 'user');
            } else {
                $error = 'E-mail ou senha incorretos';
            }
        }
        
        $this->view('auth/login', [
            'title' => 'Login',
            'error' => $error ?? null,
            'layout' => false
        ]);
    }
    
    public function register()
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('user');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->auth->register($_POST);
            
            if ($result['success']) {
                $this->redirect('user');
            } else {
                $error = $result['message'];
            }
        }
        
        $this->view('auth/register', [
            'title' => 'Criar Conta',
            'error' => $error ?? null,
            'layout' => false
        ]);
    }
    
    public function logout()
    {
        $this->auth->logout();
        $this->redirect('/');
    }
}