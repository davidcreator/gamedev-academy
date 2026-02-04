<?php
// core/Application.php

namespace Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class Application
{
    private static ?Application $instance = null;
    private Router $router;
    private Request $request;
    private Response $response;
    private ?Database $database = null;
    private ?Logger $logger = null;
    private array $config = [];

    public function __construct()
    {
        self::$instance = $this;
        
        $this->loadConfiguration();
        $this->initializeLogger();
        $this->initializeDatabase();
        
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfiguration(): void
    {
        $configFiles = ['app', 'database', 'mail'];
        
        foreach ($configFiles as $file) {
            $path = CONFIG_PATH . "/{$file}.php";
            if (file_exists($path)) {
                $this->config[$file] = require $path;
            }
        }
    }

    private function initializeLogger(): void
    {
        $this->logger = new Logger('gamedev');
        
        $logPath = STORAGE_PATH . '/logs/app.log';
        $this->logger->pushHandler(
            new RotatingFileHandler($logPath, 30, Logger::DEBUG)
        );
    }

    private function initializeDatabase(): void
    {
        if ($this->isInstalled()) {
            $this->database = Database::getInstance();
        }
    }

    public function run(): void
    {
        try {
            // Verificar se está instalado
            if (!$this->isInstalled() && !$this->isInstallerRoute()) {
                $this->redirectToInstaller();
                return;
            }

            // Carregar rotas
            $this->loadRoutes();
            
            // Despachar a requisição
            $this->router->dispatch();
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    private function loadRoutes(): void
    {
        $routeFiles = ['web', 'admin', 'api'];
        
        foreach ($routeFiles as $file) {
            $path = ROOT_PATH . "/routes/{$file}.php";
            if (file_exists($path)) {
                $router = $this->router;
                require $path;
            }
        }
    }

    public function isInstalled(): bool
    {
        return file_exists(STORAGE_PATH . '/installed.lock') && 
               file_exists(ROOT_PATH . '/.env');
    }

    private function isInstallerRoute(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return strpos($uri, '/install') === 0;
    }

    private function redirectToInstaller(): void
    {
        header('Location: /install/');
        exit;
    }

    private function handleException(\Exception $e): void
    {
        $this->logger->error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        if (env('APP_DEBUG', false)) {
            throw $e;
        }

        $this->response->setStatusCode(500);
        View::render('errors/500', ['message' => 'Erro interno do servidor']);
    }

    public function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    public function getRouter(): Router { return $this->router; }
    public function getRequest(): Request { return $this->request; }
    public function getResponse(): Response { return $this->response; }
    public function getDatabase(): ?Database { return $this->database; }
    public function getLogger(): Logger { return $this->logger; }
}