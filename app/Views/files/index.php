<?php
use App\Core\CSRF;
use App\Core\Config;
use App\Core\Helpers;

$cdnBase = Config::cdnBaseUrl();
?>
<!-- Upload -->
<section class="rounded-2xl border border-slate-800 bg-gradient-to-b from-slate-900/80 to-slate-900/40 p-5 shadow-xl shadow-black/30">
  <h2 class="text-xl font-semibold tracking-tight">Téléverser un fichier</h2>
  <p class="mt-1 text-sm text-slate-400">Images (png/jpg/webp/gif/svg), vidéos (mp4/webm/ogg), PDF.</p>

  <form class="mt-4 grid gap-4 sm:grid-cols-2" action="/files/upload" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(CSRF::token(), ENT_QUOTES) ?>">
    <!-- Indication au navigateur (non sécurisant côté serveur, mais utile côté client) -->
    <input type="hidden" name="MAX_FILE_SIZE" value="<?= htmlspecialchars((string)(getenv('MAX_UPLOAD_BYTES') ?: 104857600), ENT_QUOTES) ?>">

    <!-- Fichier -->
    <div>
      <label for="fileInput" class="block text-sm text-slate-300">Fichier</label>
      <input id="fileInput" type="file" name="file" required
             accept="image/*,video/*,application/pdf"
             class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-slate-100 outline-none
                    file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-slate-950
                    hover:border-slate-700">
    </div>

    <!-- Alias -->
    <div>
      <label for="alias" class="block text-sm text-slate-300">Alias (lien ancré)</label>
      <input id="alias" type="text" name="alias" placeholder="ex : logo-header"
             inputmode="latin" pattern="^[A-Za-z0-9_-]{1,100}$" maxlength="100"
             autocomplete="off" spellcheck="false"
             aria-describedby="aliasHelp"
             class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-slate-100 outline-none focus:border-primary/60">
      <p id="aliasHelp" class="mt-1 text-xs text-slate-500">
        Lettres/chiffres/“-”/“_”. Téléverser avec le même alias remplace le fichier.
      </p>
    </div>

    <!-- Envoyer -->
    <div class="sm:col-span-2">
      <button type="submit"
              class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 font-semibold text-slate-950 transition hover:brightness-110">
        Envoyer
      </button>
    </div>
  </form>
</section>


  <script>
    function showSelectedFile(input){
      const name = input.files && input.files[0] ? input.files[0].name : 'No file selected';
      document.getElementById('uploadFileName').textContent = name;
    }
  </script>
</section>


<!-- Toolbar -->
<section class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
  <div class="inline-flex rounded-xl border border-slate-800 bg-slate-900/60 p-1">
    <button class="rounded-lg px-3 py-1.5 text-sm hover:bg-slate-800/60 bg-slate-900/40 border border-transparent"
            data-kind-tab="all" onclick="setKind('all'); return false;">Tous</button>
    <button class="rounded-lg px-3 py-1.5 text-sm hover:bg-slate-800/60 bg-slate-900/40 border border-transparent"
            data-kind-tab="image" onclick="setKind('image'); return false;">Images</button>
    <button class="rounded-lg px-3 py-1.5 text-sm hover:bg-slate-800/60 bg-slate-900/40 border border-transparent"
            data-kind-tab="video" onclick="setKind('video'); return false;">Vidéos</button>
    <button class="rounded-lg px-3 py-1.5 text-sm hover:bg-slate-800/60 bg-slate-900/40 border border-transparent"
            data-kind-tab="other" onclick="setKind('other'); return false;">Docs</button>
  </div>

  <div class="relative">
    <input id="filterInput" oninput="applyFilters()" placeholder="Rechercher par alias ou nom…"
      class="w-full rounded-xl border border-slate-800 bg-slate-900/60 px-3 py-2 text-slate-100 outline-none sm:w-80 focus:border-primary/60">
    <svg class="pointer-events-none absolute right-3 top-2.5 h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <circle cx="11" cy="11" r="7" stroke-width="1.5"/><path d="M20 20l-3-3" stroke-width="1.5"/>
    </svg>
  </div>
</section>

<!-- Grid -->
<section class="mt-4">
  <?php if (empty($files)): ?>
    <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-8 text-center text-slate-400">
      Aucun fichier pour l’instant. Uploade ton premier média !
    </div>
  <?php else: ?>
  <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($files as $f):
      $publicUrl = htmlspecialchars($cdnBase . $f['stored_name']);
      $kind = $f['kind'];
      $cb = $publicUrl . '?v=' . $f['sha256'];
      $id = (int)$f['id'];
      $sizeLabel = Helpers::humanMB((int)$f['size_bytes']);
    ?>
    <article data-file-card data-kind="<?= htmlspecialchars($kind) ?>"
             data-alias="<?= htmlspecialchars($f['alias']) ?>" data-name="<?= htmlspecialchars($f['original_name']) ?>"
             class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 shadow-xl shadow-black/20 ring-1 ring-transparent transition hover:border-slate-700 hover:ring-slate-700/20">

      <div class="relative aspect-video overflow-hidden w-full bg-gradient-to-b from-slate-900 to-slate-950">
        <?php if ($kind === 'image'): ?>
          <img class="h-full w-full object-contain p-3" src="<?= $publicUrl ?>" alt="<?= htmlspecialchars($f['alias']) ?>">
        <?php elseif ($kind === 'video'): ?>
          <video class="h-full w-full object-contain" src="<?= $publicUrl ?>" controls></video>
        <?php else: ?>
          <div class="flex h-full items-center justify-center text-5xl">📄</div>
        <?php endif; ?>
        <div class="absolute left-3 top-3 rounded-lg bg-slate-950/70 px-2 py-1 text-[10px] uppercase tracking-wide text-slate-300 ring-1 ring-slate-800">
          <?= htmlspecialchars($kind) ?>
        </div>
      </div>

      <div class="p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="truncate text-sm font-semibold"><?= htmlspecialchars($f['alias']) ?></div>
            <div class="truncate text-xs text-slate-400"><?= htmlspecialchars($f['original_name']) ?></div>
          </div>
          <div class="shrink-0 rounded-lg bg-slate-900/60 px-2 py-1 text-xs text-slate-400 ring-1 ring-slate-800">
            <?= $sizeLabel ?>
          </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
          <a href="<?= $publicUrl ?>" target="_blank"
             class="rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-1.5 text-xs hover:border-slate-700">Ouvrir</a>

          <details class="group relative">
            <summary class="list-none rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-1.5 text-xs hover:border-slate-700 cursor-pointer inline-flex items-center gap-1">
              Liens
              <svg class="h-3.5 w-3.5 opacity-70" viewBox="0 0 20 20" fill="currentColor"><path d="M5.25 7.5L10 12.25 14.75 7.5"/></svg>
            </summary>
            <div class="mt-2 rounded-xl border border-slate-800 bg-slate-950/80 p-3 text-xs shadow-2xl shadow-black/40">
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <input class="w-full rounded-lg border border-slate-800 bg-slate-950 px-2 py-1" type="text" readonly value="<?= $publicUrl ?>">
                  <button class="shrink-0 rounded-lg border border-slate-800 bg-slate-900/70 px-2 py-1 hover:border-slate-700"
                          onclick="navigator.clipboard.writeText('<?= $publicUrl ?>')">Copier URL</button>
                </div>
                <div class="flex items-center gap-2">
                  <input class="w-full rounded-lg border border-slate-800 bg-slate-950 px-2 py-1" type="text" readonly value="<?= $cb ?>">
                  <button class="shrink-0 rounded-lg border border-slate-800 bg-slate-900/70 px-2 py-1 hover:border-slate-700"
                          onclick="navigator.clipboard.writeText('<?= $cb ?>')">URL cache-bust</button>
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                  <button class="rounded-lg border border-slate-800 bg-slate-900/70 px-2 py-1 hover:border-slate-700"
                          onclick="navigator.clipboard.writeText('<img src=&quot;<?= $publicUrl ?>&quot; alt=&quot;&quot; loading=&quot;lazy&quot;>')">&lt;img&gt;</button>
                  <button class="rounded-lg border border-slate-800 bg-slate-900/70 px-2 py-1 hover:border-slate-700"
                          onclick="navigator.clipboard.writeText('<video src=&quot;<?= $publicUrl ?>&quot; controls></video>')">&lt;video&gt;</button>
                  <button class="rounded-lg border border-slate-800 bg-slate-900/70 px-2 py-1 hover:border-slate-700"
                          onclick="navigator.clipboard.writeText('![](<?= $publicUrl ?>)')">Markdown</button>
                  <button class="rounded-lg border border-slate-800 bg-slate-900/70 px-2 py-1 hover:border-slate-700"
                          onclick="navigator.clipboard.writeText('background-image: url(<?= $publicUrl ?>);')">CSS bg</button>
                </div>
              </div>
            </div>
          </details>

          <form action="/files/replace" method="post" enctype="multipart/form-data" class="inline">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(CSRF::token()) ?>">
            <input type="hidden" name="alias" value="<?= htmlspecialchars($f['alias']) ?>">
            <input id="repl-<?= $id ?>" type="file" name="file" class="hidden" accept="image/*,video/*,application/pdf" onchange="this.form.submit()">
            <label for="repl-<?= $id ?>" class="cursor-pointer rounded-lg border border-slate-800 bg-slate-900/70 px-3 py-1.5 text-xs hover:border-slate-700">Remplacer (garder l’URL)</label>
          </form>

          <?php if ($kind === 'image'): ?>
          <form action="/files/optimize" method="post" class="inline" onsubmit="return confirm('Optimiser cette image ?');">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(CSRF::token()) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit" class="rounded-lg border border-emerald-900 bg-emerald-900/30 px-3 py-1.5 text-xs text-emerald-100 hover:bg-emerald-900/40">
              Optimiser
            </button>
          </form>
          <?php endif; ?>

          <form action="/files/delete" method="post" class="ml-auto inline" onsubmit="return confirm('Supprimer ce fichier ?');">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(CSRF::token()) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit" class="rounded-lg border border-red-900 bg-red-900/30 px-3 py-1.5 text-xs text-red-100 hover:bg-red-900/40">
              Supprimer
            </button>
          </form>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <script>
    let currentKind='all';
    function setKind(k){currentKind=k;applyFilters();}
    function applyFilters(){
      const q=(document.getElementById('filterInput').value||'').toLowerCase();
      document.querySelectorAll('[data-file-card]').forEach(c=>{
        const okK=(currentKind==='all'||c.dataset.kind===currentKind);
        const okQ=(!q||(c.dataset.alias+' '+c.dataset.name).toLowerCase().includes(q));
        c.classList.toggle('hidden',!(okK&&okQ));
      });
    }
    setKind('all');
  </script>
  <?php endif; ?>
</section>
