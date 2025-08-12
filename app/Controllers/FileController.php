<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Core\View;
use App\Models\FileItem;

final class FileController {
  public function index(): void {
    Auth::requireLogin();
    $files = FileItem::all();
    View::render('files/index', ['title'=>'Fichiers', 'files'=>$files]);
  }

  public function upload(): void {
    Auth::requireLogin();
    CSRF::check($_POST['csrf'] ?? '');
    $aliasRaw = trim($_POST['alias'] ?? '');
    $alias = $aliasRaw !== '' ? $this->validateAlias($aliasRaw) : '';

    $f = $_FILES['file'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) $this->badReq('Upload invalide');

    $maxBytes = (int) (getenv('MAX_UPLOAD_BYTES') ?: 100*1024*1024);
    if ($f['size'] > $maxBytes) $this->badReq('Fichier trop volumineux');

    $mime = Helpers::finfoMime($f['tmp_name']);
    $allowed = Config::allowedMime();
    if (!isset($allowed[$mime])) $this->badReq('Type de fichier non autorisé');

    $ext = $allowed[$mime];
    $kind = Helpers::kindFromMime($mime);
    $cdnDir = Config::cdnDir();

    $token = bin2hex(random_bytes(12));
    $base = $alias !== '' ? $alias : $token;
    $stored = $base . '.' . $ext;
    $target = $cdnDir . DIRECTORY_SEPARATOR . $stored;

    if (!is_dir($cdnDir)) @mkdir($cdnDir, 0775, true);

    if ($alias !== '') {
      foreach (glob($cdnDir . DIRECTORY_SEPARATOR . $alias . '.*') as $old) {
        if (basename($old) !== $stored) @unlink($old);
      }
    }

    if (!@move_uploaded_file($f['tmp_name'], $target)) $this->serverErr('Échec écriture fichier');

    @chmod($target, 0664);
    $sha = hash_file('sha256', $target);
    $size = filesize($target) ?: (int)$f['size'];

    $row = [
      'alias' => ($alias !== '' ? $alias : $token),
      'original_name' => $f['name'],
      'stored_name'   => $stored,
      'mime_type'     => $mime,
      'size_bytes'    => $size,
      'uploaded_by'   => $_SESSION['uid'] ?? null,
      'sha256'        => $sha,
      'kind'          => $kind,
    ];

    if ($alias !== '') FileItem::upsertByAlias($alias, $row);
    else FileItem::insert($row);

    header('Location: /');
  }

  public function replace(): void {
    Auth::requireLogin();
    CSRF::check($_POST['csrf'] ?? '');
    $alias = $this->validateAlias((string)($_POST['alias'] ?? ''));
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) $this->badReq('Fichier manquant');
    $_POST['alias'] = $alias;
    $this->upload();
  }

  public function delete(): void {
    Auth::requireLogin();
    CSRF::check($_POST['csrf'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    $f = FileItem::find($id);
    if (!$f) $this->badReq('Fichier introuvable');

    $cdn = Config::cdnDir();
    $stored = $f['stored_name'];
    if (strpos($stored, '/') !== false || strpos($stored, '\\') !== false) $this->badReq('Nom invalide');

    $path = $cdn . DIRECTORY_SEPARATOR . $stored;
    if (is_file($path)) @unlink($path);
    foreach (glob($cdn . DIRECTORY_SEPARATOR . $f['alias'] . '.*') as $old) @unlink($old);

    FileItem::delete($id);
    header('Location: /');
  }

	public function optimize(): void {
  \App\Core\Auth::requireLogin();
  \App\Core\CSRF::check($_POST['csrf'] ?? '');

  $id = (int)($_POST['id'] ?? 0);
  $f = \App\Models\FileItem::find($id);
  if (!$f) $this->badReq('Fichier introuvable');

  $mime = $f['mime_type'];
  if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
    $this->badReq('Optimisation JPEG/PNG/WebP uniquement');
  }

  $path = \App\Core\Config::cdnDir() . DIRECTORY_SEPARATOR . $f['stored_name'];
  if (!is_file($path) || !is_writable($path)) $this->serverErr('Fichier indisponible ou non modifiable');

  try {
    $res = \App\Services\ImageOptimizer::run($path, $mime);
    if (!empty($res['changed'])) {
      \App\Models\FileItem::updateById($id, [
        'original_name' => $f['original_name'],
        'stored_name'   => $f['stored_name'],
        'mime_type'     => $f['mime_type'],
        'size_bytes'    => $res['size'],
        'uploaded_by'   => $_SESSION['uid'] ?? null,
        'sha256'        => $res['sha256'],
        'kind'          => $f['kind'],
      ]);
      $_SESSION['flash'] = 'Optimisé : -'.round(100 * ($res['old'] - $res['size']) / max(1,$res['old'])).'%';
    }
  } catch (\Throwable $e) {
    error_log('Optimize error: '.$e->getMessage());
    $this->serverErr('Échec optimisation');
  }

  header('Location: /');
}


  private function validateAlias(string $a): string {
    $a = trim($a);
    if ($a === '' || !preg_match('/^[A-Za-z0-9_-]{1,100}$/', $a)) $this->badReq('Alias invalide');
    return $a;
  }
  private function badReq(string $m): void { http_response_code(400); echo $m; exit; }
  private function serverErr(string $m): void { http_response_code(500); echo $m; exit; }
}
