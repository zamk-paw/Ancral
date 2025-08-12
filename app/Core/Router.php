<?php
namespace App\Core;

final class Router {
  private array $routes = ['GET'=>[], 'POST'=>[]];
  public function get(string $path, $handler): void { $this->routes['GET'][$path] = $handler; }
  public function post(string $path, $handler): void { $this->routes['POST'][$path] = $handler; }
  public function dispatch(): void {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $routes = $this->routes[$method] ?? [];
    $handler = $routes[$uri] ?? null;
    if (!$handler) { http_response_code(404); echo 'Not Found'; return; }
    if (is_array($handler)) { [$class, $m] = $handler; (new $class())->$m(); }
    else { $handler(); }
  }
}
