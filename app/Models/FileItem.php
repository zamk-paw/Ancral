<?php
namespace App\Models;
use App\Core\DB;

final class FileItem {
  public static function all(): array {
    return DB::pdo()->query('SELECT * FROM files ORDER BY created_at DESC')->fetchAll();
  }
  public static function find(int $id): ?array {
    $st = DB::pdo()->prepare('SELECT * FROM files WHERE id=?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
  }
  public static function findByAlias(string $alias): ?array {
    $st = DB::pdo()->prepare('SELECT * FROM files WHERE alias=?');
    $st->execute([$alias]);
    return $st->fetch() ?: null;
  }
  public static function insert(array $f): void {
    DB::pdo()->prepare("
      INSERT INTO files(alias, original_name, stored_name, mime_type, size_bytes, uploaded_by, sha256, kind, created_at, updated_at)
      VALUES(?,?,?,?,?,?,?,?,?,?)
    ")->execute([
      $f['alias'], $f['original_name'], $f['stored_name'], $f['mime_type'],
      $f['size_bytes'], $f['uploaded_by'], $f['sha256'], $f['kind'], date('c'), date('c')
    ]);
  }
  public static function updateById(int $id, array $f): void {
    DB::pdo()->prepare("
      UPDATE files SET original_name=?, stored_name=?, mime_type=?, size_bytes=?, uploaded_by=?, sha256=?, kind=?, updated_at=? WHERE id=?
    ")->execute([
      $f['original_name'], $f['stored_name'], $f['mime_type'], $f['size_bytes'],
      $f['uploaded_by'], $f['sha256'], $f['kind'], date('c'), $id
    ]);
  }
  public static function upsertByAlias(string $alias, array $f): void {
    $row = self::findByAlias($alias);
    if ($row) self::updateById((int)$row['id'], $f);
    else self::insert($f);
  }
  public static function delete(int $id): void {
    DB::pdo()->prepare('DELETE FROM files WHERE id=?')->execute([$id]);
  }
}
