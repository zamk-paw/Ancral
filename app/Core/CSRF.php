<?php
namespace App\Core;

final class CSRF {
  public static function token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
  }
  public static function check(?string $t): void {
    if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
      http_response_code(400); echo 'Invalid CSRF token.'; exit;
    }
  }
}
