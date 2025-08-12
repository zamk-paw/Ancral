<?php
declare(strict_types=1);

spl_autoload_register(function($class) {
  $prefix = 'App\\';
  $base = __DIR__ . '/';
  if (strpos($class, $prefix) !== 0) return;
  $rel = str_replace('\\', '/', substr($class, strlen($prefix)));
  $file = $base . $rel . '.php';
  if (is_file($file)) require $file;
});

use App\Core\Config;
use App\Core\DB;

// Sessions strictes
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('UPLD_' . substr(hash('sha256', Config::env('APP_SECRET', 'devsecret')), 0, 8));
session_start(['cookie_samesite' => 'Strict']);

$pdo = DB::pdo();
$pdo->exec('PRAGMA journal_mode = WAL;');
$pdo->exec('PRAGMA foreign_keys = ON;');

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS files (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  alias TEXT UNIQUE NOT NULL,
  original_name TEXT NOT NULL,
  stored_name TEXT NOT NULL,
  mime_type TEXT NOT NULL,
  size_bytes INTEGER NOT NULL,
  uploaded_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
  sha256 TEXT NOT NULL,
  kind TEXT NOT NULL,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_files_created ON files(created_at DESC);
");

$adminEmail = Config::env('APP_ADMIN_EMAIL', 'admin@example.com');
$adminPass  = Config::env('APP_ADMIN_PASSWORD', 'changeMeNow!');
$st = $pdo->prepare('SELECT id FROM users WHERE email=?');
$st->execute([$adminEmail]);
if (!$st->fetch()) {
  $pdo->prepare('INSERT INTO users(email, password_hash, created_at) VALUES(?,?,?)')
      ->execute([$adminEmail, password_hash($adminPass, PASSWORD_DEFAULT), date('c')]);
}
