<?php

class Router {
    private $version;
    private $basePath;
    private $routes = [];

    public function __construct($version = 'v1', $basePath = '') {
        $this->version = $version;
        $this->basePath = $basePath;
    }

    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Limpiar el prefijo base del servidor
        $uri = str_replace($this->basePath, '', $uri);

        // Eliminar prefijos /v1 o /v2 remanentes
        $uri = preg_replace('#^/v[12]#', '', $uri);

        // Si la URI queda vacía, asignamos /
        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchUrl($route['path'], $uri, $matches)) {
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode([
            "message" => "Ruta no encontrada",
            "uri_procesada" => $uri
        ]);
    }

    private function matchUrl($routePath, $uri, &$matches) {
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $foundMatches)) {
            array_shift($foundMatches);
            $matches = $foundMatches;
            return true;
        }

        return false;
    }
}
?>
