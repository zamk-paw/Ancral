# Ancral

**Ancral** est un mini gestionnaire d’assets auto-hébergé (images, vidéos, PDF, etc.) avec des **liens ancrés**.
Uploader un fichier, récupérer des **liens prêts à l’emploi** (direct, cache-bust, HTML/Markdown/CSS), **remplacer** un asset en gardant **la même URL**, et **optimiser** les images (JPEG/PNG/WebP) — le tout dans **un seul conteneur Docker**.

* Single-container (Apache + PHP 8.3)
* Base **SQLite** (zéro dépendance externe)
* **CDN statique** servi sous `/cdn/` depuis un dossier hôte (**/share**) — PHP désactivé
* **Alias** pour conserver un **lien ancré** et remplaçable
* **Optimisation d’images** (GD) avec gestion mémoire robuste
* **UI Tailwind** moderne (recherche, filtres, menu “Liens”, remplacer/supprimer)

---

## Pourquoi ?

Ancral n’est pas une CDN géo-distribuée.
C’est un **hub d’assets** simple pour des projets web. Les mêmes URLs peuvent être réutilisées sur plusieurs sites.
Un visuel modifié ici est **mis à jour partout**.

---

## Démarrage rapide

```bash
# 1) Préparer le dossier CDN (sur l’hôte)
sudo mkdir -p /share
sudo chown -R 33:33 /share         # www-data
sudo chmod -R 775 /share

# 2) Variables d’environnement (optionnel)
cp .env.example .env
# éditer .env pour définir l’admin et un APP_SECRET fort (openssl rand -hex 64)

# 3) Build & run
docker compose up -d --build

# 4) Ouvrir
# App :  http://localhost:8080/
# CDN :  http://localhost:8080/cdn/<nom-de-fichier>
```
---

## Fonctionnalités

* **Upload multi-formats** : images (png/jpg/webp/gif/svg), vidéos (mp4/webm/ogg), PDF.
* **Alias (lien ancré)** : `logo-header.jpg` → re-upload sur le même alias = **remplacement sans changer l’URL**.
* **Menu “Liens”** : copie **URL directe**, **URL cache-bust** (`?v=<sha256>`), snippets **HTML**, **Markdown**, **CSS**.
* **Optimiser** (images) : recompression JPEG/PNG/WebP (format préservé, URL inchangée).
* **UI Tailwind** : filtre par type (Images/Vidéos/Docs), recherche, actions rapides.

---

## Architecture

* **App MVC** servie depuis `public/` (front-controller, routes, contrôleurs, modèles, vues Tailwind).
* **CDN statique** servi sur `/cdn/` → répertoire conteneur `/var/www/cdn-public` (bind-mount sur l’hôte `/share`).
* **SQLite** : `/var/www/data/app.db` (WAL + foreign keys).
* **Séparation claire** : code ≠ données (sécurité/maintenance).

Arborescence (résumé) :

```
app/
  Core/ (Config, DB, Router, CSRF, View, Helpers)
  Controllers/ (AuthController, FileController)
  Models/ (User, FileItem)
  Services/ (ImageOptimizer)
  Views/ (layout, auth/login, files/index)
public/
  .htaccess
  index.php
/share (hôte) → /var/www/cdn-public (servi sous /cdn/)
```

---

## Variables d’environnement

| Variable             | Rôle                                              | Défaut                       |
| -------------------- | ------------------------------------------------- | ---------------------------- |
| `APP_ADMIN_EMAIL`    | Email admin (seed au 1er démarrage)               | `admin@example.com`          |
| `APP_ADMIN_PASSWORD` | Mot de passe admin (seed au 1er démarrage)        | `changeMeNow!`               |
| `APP_SECRET`         | Secret sessions/CSRF (ex. `openssl rand -hex 64`) | `devsecret-change-me`        |
| `CDN_BASE_URL`       | Base publique pour générer les liens              | `http://localhost:8080/cdn/` |
| `MAX_UPLOAD_BYTES`   | Taille max d’upload (octets)                      | `104857600` (100 Mo)         |
| `TZ`                 | Fuseau horaire du conteneur                       | `Europe/Paris`               |

`.env.example` fourni.

---

## Sécurité

* **PHP désactivé** sous `/cdn/` (impossible d’exécuter un script uploadé).
* Détection **MIME** via `finfo` + **liste blanche**.
* **Alias** restreints : `^[A-Za-z0-9_-]{1,100}$`.
* **CSRF tokens** sur tous les POST.
* Sessions strictes (`HttpOnly`, `SameSite=Strict`, strict mode).
* En-têtes Apache (`X-Content-Type-Options`, `X-Frame-Options`) + cache statique.

---

## Routes

* `GET /login`, `POST /login`, `POST /logout`
* `GET /` — tableau de bord (upload + grille)
* `POST /files/upload` — upload (optionnel `alias`)
* `POST /files/replace` — remplacement **en gardant la même URL** (via alias)
* `POST /files/delete` — suppression
* `POST /files/optimize` — optimisation JPEG/PNG/WebP (URL et format conservés)

---

## Dépannage

* **403 sur /** : DocumentRoot doit pointer sur `public/` (déjà configuré).
* **500 sur Optimize** (grosse image) :

  * augmenter `memory_limit` (ex. **768M**) ;

    ```bash
    docker exec -it ancral-app php -r 'echo ini_get("memory_limit"), PHP_EOL;'
    ```
  * `/share` doit être **écrivable** par `www-data` (`33:33`).
* **Permissions** :

  ```bash
  sudo chown -R 33:33 /share
  sudo chmod -R 775 /share
  ```
* Logs :

  ```bash
  docker logs ancral-app --tail=200
  docker exec -it ancral-app bash -lc 'tail -n 200 /var/log/php_errors.log'
  ```
