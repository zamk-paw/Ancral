<?php
use App\Core\CSRF;
?>
<div class="w-full max-w-md mx-auto">
  <div class="mb-8 text-center">
    <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/15 ring-1 ring-primary/30">
      <svg class="h-6 w-6 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4M3 17l9 4 9-4"/>
      </svg>
    </div>
    <h1 class="mt-3 text-2xl font-semibold tracking-tight">Connexion</h1>
    <p class="mt-1 text-sm text-slate-400">Accéder au panneau d’upload</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="/login" class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow-xl shadow-black/30">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(CSRF::token()) ?>">
    <label class="block text-sm text-slate-300">E-mail</label>
    <input type="email" name="email" required autofocus
           class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-slate-100 outline-none focus:border-primary/60"
           placeholder="admin@exemple.com">
    <label class="mt-4 block text-sm text-slate-300">Mot de passe</label>
    <input type="password" name="password" required
           class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-slate-100 outline-none focus:border-primary/60"
           placeholder="••••••••">
    <button type="submit"
            class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-primary px-4 py-2.5 font-semibold text-slate-950 transition hover:brightness-110">
      Se connecter
    </button>
  </form>
</div>