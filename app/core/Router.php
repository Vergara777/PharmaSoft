<?php
namespace App\Core;

class Router {
    private array $routes = [];
    private string $baseUrl;

    public function __construct(string $baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function get(string $path, string $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, string $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, string $handler): void {
        // Normalize to always have a leading slash so '/' is preserved
        $normalized = '/' . ltrim($path, '/');
        // Replace path parameters like {id}
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $normalized);
        $pattern = '#^' . $regex . '$#';
        $this->routes[] = compact('method','pattern','handler');
    }

    public function dispatch(): void {
        $requestUri = $_GET['url'] ?? '/';
        $requestUri = '/' . trim($requestUri, '/');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (preg_match($route['pattern'], $requestUri, $matches)) {
                [$controllerName, $action] = explode('@', $route['handler']);
                $controllerClass = 'App\\Controllers\\' . $controllerName;
                if (!class_exists($controllerClass)) { http_response_code(404); echo 'Controller not found'; return; }
                $controller = new $controllerClass();
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                if (!method_exists($controller, $action)) { http_response_code(404); echo 'Action not found'; return; }
                $controller->$action(...array_values($params));
                return;
            }
        }
        http_response_code(404);
        echo 'Not Found';
    }
}
