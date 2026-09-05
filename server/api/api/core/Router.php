<?php
class Router
{
    private $routes = [];
    private $version;
    private $basePath;

    public function __construct($version = 'v1', $basePath = '')
    {
        $this->version = $version;
        $this->basePath = rtrim($basePath, '/');
    }

    public function addRoute($method, $path, $handler, $protected = false)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => "/api/{$this->version}" . $path,
            'handler' => $handler,
            'protected' => $protected
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!empty($this->basePath) && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        if (strpos($uri, '/index.php') === 0) {
            $uri = substr($uri, strlen('/index.php'));
        }

        // Asegurar que la URI comience con 
        $uri = '/' . ltrim($uri, '/');

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                
                if ($route['protected']) {
                    require_once __DIR__ . '/AuthMiddleware.php';

                    if (!AuthMiddleware::check()) {
                        http_response_code(401);
                        header("Content-Type: application/json");
                        echo json_encode(array(
                            "error" => "unauthorized",
                            "message" => "Token inválido, expirado o no proporcionado"
                        ));
                        return;
                    }
                }

                return call_user_func_array($route['handler'], $matches);
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Ruta no encontrada', 'uri' => $uri]);
    }
}
?>