<?php
namespace App\Core;

final class Config {
  public static function env(string $key, ?string $default=null): string {
    $v = getenv($key);
    return ($v === false || $v === '') ? ($default ?? '') : $v;
  }
  public static function cdnBaseUrl(): string {
    $base = self::env('CDN_BASE_URL', 'http://localhost:8080/cdn/');
    return rtrim($base, '/') . '/';
  }
  public static function dataDir(): string { return '/var/www/data'; }
  public static function dbPath(): string { return self::dataDir() . '/app.db'; }
  public static function cdnDir(): string { return '/var/www/cdn-public'; }
  public static function allowedMime(): array {
    return [
      'image/png' => 'png',
      'image/jpeg' => 'jpg',
      'image/webp' => 'webp',
      'image/gif' => 'gif',
      'image/svg+xml' => 'svg',
      'video/mp4' => 'mp4',
      'video/webm' => 'webm',
      'video/ogg' => 'ogv',
      'application/pdf' => 'pdf',
    ];
  }
}
