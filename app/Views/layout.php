<?php
use App\Core\Auth;
use App\Core\CSRF;
$title = $title ?? 'App';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode:'class', theme:{ extend:{ colors:{ primary:{ DEFAULT:'#22c55e' } } } } };
  </script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
  <header class="sticky top-0 z-40 border-b border-slate-800 bg-slate-950/70 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
      <div class="flex items-center gap-2">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/15 ring-1 ring-primary/30">
          <svg class="h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4M3 17l9 4 9-4"/>
          </svg>
        </div>
        <span class="text-lg font-semibold tracking-tight">Ancral</span>
      </div>
      <nav class="flex items-center gap-3">
        <?php if (Auth::check()): ?>
          <span class="hidden text-sm text-slate-400 sm:inline"><?= htmlspecialchars($user['email'] ?? '') ?></span>
          <form method="post" action="/logout">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(CSRF::token()) ?>">
            <button class="rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-1.5 text-sm hover:border-slate-700">Déconnexion</button>
          </form>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="mx-auto max-w-6xl px-4 py-6">
    <?php require $viewPath; ?>
  </main>
</body>
</html>
