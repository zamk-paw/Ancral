<?php
namespace App\Core;
use PDO;

final class DB {
  private static ?PDO $pdo = null;
  public static function pdo(): PDO {
    if (!self::$pdo) {
      $path = Config::dbPath();
      if (!is_dir(dirname($path))) { @mkdir(dirname($path), 0775, true); }
      self::$pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    }
    return self::$pdo;
  }
}
