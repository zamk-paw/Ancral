<?php
namespace App\Core;

final class View {
  public static function render(string $view, array $data=[]): void {
    extract($data, EXTR_SKIP);
    $user = Auth::user();
    $cdnBase = Config::cdnBaseUrl();
    $viewPath = __DIR__ . '/../Views/' . $view . '.php';
    require __DIR__ . '/../Views/layout.php';
  }
}
