<?php
namespace App\Models;
use App\Core\DB;

final class User {
  public static function find(int $id): ?array {
    $st = DB::pdo()->prepare('SELECT id,email,created_at FROM users WHERE id=?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
  }
  public static function findByEmail(string $email): ?array {
    $st = DB::pdo()->prepare('SELECT id,email,password_hash FROM users WHERE email=?');
    $st->execute([$email]);
    return $st->fetch() ?: null;
  }
}
