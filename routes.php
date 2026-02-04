<?php
// routes.php

return [
    // Página inicial
    '/' => 'HomeController@index',
    '/home' => 'HomeController@index',
    
    // Autenticação
    '/login' => 'AuthController@login',
    '/register' => 'AuthController@register',
    '/logout' => 'AuthController@logout',
    
    // Painel Admin
    '/admin' => 'AdminController@dashboard',
    '/admin/dashboard' => 'AdminController@dashboard',
    '/admin/users' => 'AdminController@users',
    '/admin/courses' => 'AdminController@courses',
    '/admin/news' => 'AdminController@news',
    
    // Painel Usuário
    '/user' => 'UserController@dashboard',
    '/user/dashboard' => 'UserController@dashboard',
    '/user/courses' => 'UserController@courses',
    '/user/profile' => 'UserController@profile',
    '/user/achievements' => 'UserController@achievements',
    
    // Páginas públicas
    '/courses' => 'HomeController@courses',
    '/course/{slug}' => 'HomeController@course',
    '/news' => 'HomeController@news',
];