<?php
namespace App\Core;
use App\Models\User;

final class Auth {
  public static function check(): bool { return isset($_SESSION['uid']); }
  public static function user(): ?array { return self::check() ? User::find((int)$_SESSION['uid']) : null; }
  public static function login(int $id): void { $_SESSION['uid'] = $id; }
  public static function logout(): void { session_destroy(); }
  public static function requireLogin(): void {
    if (!self::check()) { header('Location: /login'); exit; }
  }
}
