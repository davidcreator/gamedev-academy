<?php
// core/Router.php

namespace Core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    private Request $request;
    private Response $response;
    private array $routes = [];
    private array $groupStack = [];
    private array $middlewareGroups = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        
        $this->middlewareGroups = [
            'web' => [],
            'auth' => [\App\Middleware\AuthMiddleware::class],
            'admin' => [\App\Middleware\AdminMiddleware::class],
            'guest' => [\App\Middleware\GuestMiddleware::class],
        ];
    }

    public function get(string $uri, $handler): self
    {
        return $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, $handler): self
    {
        return $this->addRoute('POST', $uri, $handler);
    }

    public function put(string $uri, $handler): self
    {
        return $this->addRoute('PUT', $uri, $handler);
    }

    public function delete(string $uri, $handler): self
    {
        return $this->addRoute('DELETE', $uri, $handler);
    }

    public function any(string $uri, $handler): self
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $this->addRoute($method, $uri, $handler);
        }
        return $this;
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $uri, $handler): self
    {
        $uri = $this->applyGroupPrefix($uri);
        $middleware = $this->getGroupMiddleware();
        
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler,
            'middleware' => $middleware
        ];
        
        return $this;
    }

    private function applyGroupPrefix(string $uri): string
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        return rtrim($prefix . '/' . ltrim($uri, '/'), '/') ?: '/';
    }

    private function getGroupMiddleware(): array
    {
        $middleware = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $middlewareNames = (array) $group['middleware'];
                foreach ($middlewareNames as $name) {
                    if (isset($this->middlewareGroups[$name])) {
                        $middleware = array_merge($middleware, $this->middlewareGroups[$name]);
                    } else {
                        $middleware[] = $name;
                    }
                }
            }
        }
        return array_unique($middleware);
    }

    public function dispatch(): void
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['uri'], [
                    'handler' => $route['handler'],
                    'middleware' => $route['middleware']
                ]);
            }
        });

        $httpMethod = $this->request->method();
        $uri = $this->request->uri();

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->handleNotFound();
                break;
                
            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->handleMethodNotAllowed($routeInfo[1]);
                break;
                
            case Dispatcher::FOUND:
                $this->handleFound($routeInfo[1], $routeInfo[2]);
                break;
        }
    }

    private function handleNotFound(): void
    {
        $this->response->setStatusCode(404);
        View::render('errors/404');
    }

    private function handleMethodNotAllowed(array $allowedMethods): void
    {
        $this->response->setStatusCode(405);
        $this->response->setHeader('Allow', implode(', ', $allowedMethods));
        View::render('errors/405');
    }

    private function handleFound(array $routeData, array $vars): void
    {
        $handler = $routeData['handler'];
        $middleware = $routeData['middleware'];

        // Executar middlewares
        foreach ($middleware as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                $result = $middlewareInstance->handle($this->request);
                
                if ($result === false) {
                    return;
                }
            }
        }

        // Executar o handler
        if (is_callable($handler)) {
            call_user_func_array($handler, $vars);
        } elseif (is_string($handler)) {
            $this->callControllerAction($handler, $vars);
        } elseif (is_array($handler)) {
            $this->callControllerAction($handler[0] . '@' . $handler[1], $vars);
        }
    }

    private function callControllerAction(string $handler, array $vars): void
    {
        [$controllerClass, $method] = explode('@', $handler);
        
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller {$controllerClass} não encontrado");
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Método {$method} não encontrado em {$controllerClass}");
        }

        call_user_func_array([$controller, $method], $vars);
    }
}