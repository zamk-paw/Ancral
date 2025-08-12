<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;

$router = new Router();

// Auth
$router->get('/login', [App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [App\Controllers\AuthController::class, 'login']);
$router->post('/logout', [App\Controllers\AuthController::class, 'logout']);

// Files (protected)
$router->get('/', [App\Controllers\FileController::class, 'index']);
$router->post('/files/upload', [App\Controllers\FileController::class, 'upload']);
$router->post('/files/replace', [App\Controllers\FileController::class, 'replace']);
$router->post('/files/delete', [App\Controllers\FileController::class, 'delete']);
$router->post('/files/optimize', [App\Controllers\FileController::class, 'optimize']);

$router->dispatch();
