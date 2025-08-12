<?php
namespace App\Core;

final class Helpers {
  public static function finfoMime(string $file): string {
    $fi = new \finfo(FILEINFO_MIME_TYPE);
    return $fi->file($file) ?: 'application/octet-stream';
  }
  public static function kindFromMime(string $mime): string {
    if (strpos($mime, 'image/') === 0) return 'image';
    if (strpos($mime, 'video/') === 0) return 'video';
    return 'other';
  }
  public static function humanMB(int $bytes): string {
    return number_format($bytes / (1024*1024), 2, ',', ' ') . ' Mo';
  }
}
