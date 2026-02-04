<?php
// core/App.php

class App
{
    protected $routes = [];
    protected $auth;
    
    public function __construct()
    {
        $this->auth = new Auth();
        $this->loadRoutes();
    }
    
    protected function loadRoutes()
    {
        if (file_exists(ROOT . '/routes.php')) {
            $routes = require ROOT . '/routes.php';
            $this->routes = $routes;
        }
    }
    
    public function run()
    {
        $url = $this->parseUrl();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Rota padrão
        $route = $url ?: '/';
        
        // Procurar rota correspondente
        foreach ($this->routes as $pattern => $handler) {
            if ($this->matchRoute($pattern, $route, $matches)) {
                $this->handleRoute($handler, $matches);
                return;
            }
        }
        
        // 404
        $this->show404();
    }
    
    protected function matchRoute($pattern, $route, &$matches)
    {
        // Converter {param} para regex
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $route, $matches)) {
            array_shift($matches);
            return true;
        }
        
        return false;
    }
    
    protected function handleRoute($handler, $params = [])
    {
        // Se for string, assumir Controller@method
        if (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                list($controller, $method) = explode('@', $handler);
                $this->callController($controller, $method, $params);
            } else {
                // É uma view direta
                View::render($handler);
            }
        }
        // Se for callable
        elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
        }
    }
    
    protected function callController($controllerName, $method, $params = [])
    {
        $controllerClass = "\\App\\Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            require_once APP_PATH . "/Controllers/{$controllerName}.php";
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            $this->show404();
            return;
        }
        
        call_user_func_array([$controller, $method], $params);
    }
    
    protected function parseUrl()
    {
        $url = $_GET['url'] ?? '';
        $url = trim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        // Remover /public se existir
        $url = str_replace('public/', '', $url);
        
        return $url ? '/' . $url : '/';
    }
    
    protected function show404()
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Página não encontrada']);
    }
}