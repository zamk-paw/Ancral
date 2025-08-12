<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\View;
use App\Models\User;

final class AuthController {
  public function showLogin(): void {
    if (Auth::check()) { header('Location: /'); exit; }
    View::render('auth/login', ['title' => 'Connexion']);
  }
  public function login(): void {
    CSRF::check($_POST['csrf'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $user = User::findByEmail($email);
    if ($user && password_verify($pass, $user['password_hash'])) {
      Auth::login((int)$user['id']);
      header('Location: /'); exit;
    }
    View::render('auth/login', ['title'=>'Connexion', 'error'=>'Identifiants invalides.']);
  }
  public function logout(): void {
    CSRF::check($_POST['csrf'] ?? '');
    Auth::logout();
    header('Location: /login');
  }
}
