<?php
// core/View.php

class View
{
    public static function render($view, $data = [])
    {
        extract($data);
        
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            die("View {$view} não encontrada");
        }
        
        // Se tiver layout definido
        $layout = $data['layout'] ?? 'main';
        
        if ($layout && file_exists(VIEWS_PATH . "/layouts/{$layout}.php")) {
            $content = $viewFile;
            require VIEWS_PATH . "/layouts/{$layout}.php";
        } else {
            require $viewFile;
        }
    }
}