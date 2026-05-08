# TMail NeoReborn

Self-hosted disposable / temporary email service built on Laravel 12 + Livewire 3,
with a global neobrutalism theme, public landing page, and admin email management.

> Designed for **plug-and-play** deployment on shared hosting — no terminal, no `npm`,
> no `composer install` required after upload. Just upload, fill in `.env`, and open
> `/installer`.

---

## Features

- **Public landing page** — guests can open any pre-existing inbox via an email-input
  box or `/mailbox/{email}` URL. They can read, copy, and refresh — but not create or
  delete (auth-gated).
- **Admin email management** — `/admin/emails` lets admins create accounts in three
  modes: random username on a chosen domain, bulk random, or manually-typed
  usernames. Reserved usernames (RFC 2142: postmaster, admin, abuse, etc.) are
  blocked automatically.
- **Neobrutalism theme** — global re-skin of every Blade component
  (`x-button`, `x-card`, `x-input`, etc.), so every admin and auth page inherits the
  3 px borders + hard shadow look without page-by-page rewrites.
- **One-screen installer** — DB credentials → license key → domains → admin account.
  Migrations, `optimize:clear`, and `storage:link` are run automatically via PHP
  (`Artisan::call`) so no terminal is needed.
- **Post-install health check** — the installer's final screen and `/admin/maintenance`
  both show pass/fail rows for migrations, storage permissions, front-end assets,
  and the public storage symlink.
- **Admin maintenance page** — `/admin/maintenance` exposes one-click buttons for
  `migrate`, `optimize:clear`, and `storage:link --force`. Useful when you upload an
  update and don't have SSH access.
- **Iframe sandboxing** on email body preview, **rate-limit** on guest mailbox
  lookups, and a **fillable-safe** `Email` model.

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP         | ≥ 8.2 (8.3 recommended) |
| Database    | MySQL ≥ 5.7 *or* MariaDB ≥ 10.3 *or* SQLite ≥ 3.8.8 |
| Web server  | Apache (with `mod_rewrite`) or Nginx |
| Composer    | 2.x — only needed once, to vendor dependencies before zipping |
| Node + npm  | Only needed to rebuild front-end assets after editing `resources/css/app.css` etc. |

PHP extensions: `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `JSON`, `Mbstring`,
`OpenSSL`, `PCRE`, `PDO`, `Tokenizer`, `XML`, `imap` *(only if engine=imap)*.

---

## Plug-and-play deploy (shared hosting)

### Before uploading (once, on a workstation that has Composer)

```bash
git clone https://github.com/Dexornit/TmailNeoReborn.git
cd TmailNeoReborn
composer install --no-dev --optimize-autoloader
# (optional) npm install && npm run build
# pre-built assets are already committed under public/build/
zip -r tmail-deploy.zip . -x ".git/*" "node_modules/*" "tests/*" ".env"
```

### On the shared host

1. **Upload + extract** `tmail-deploy.zip` into your `public_html` (or your
   document-root subdirectory).
2. **Copy** `.env.example` to `.env` and edit:
   - `APP_KEY=` (run once locally `php artisan key:generate --show` and paste here,
     or leave blank — the installer will not generate it for you so set it before
     hitting `/installer`).
   - `APP_URL=https://yourdomain.com`
   - `DB_*` — leave blank if you don't know yet, the installer asks again.
3. **Open `https://yourdomain.com/installer`** in a browser.
4. **Walk through the 4 steps**:
   1. Database connection details
   2. App name + license key (any non-empty string — license is bypassed)
   3. Domains + engine (TMail Delivery or IMAP)
   4. Admin account (name, email, password)
5. The installer auto-runs `migrate:fresh`, `optimize:clear`, `storage:link`,
   and shows a **health-check report** at the end.
6. Click **Visit TMail – Admin Panel** and log in.

If anything's broken later (cache stuck, schema drift, broken symlink), the admin
can hit `/admin/maintenance` and run the same commands without ever needing SSH.

---

## Local development

```bash
git clone https://github.com/Dexornit/TmailNeoReborn.git
cd TmailNeoReborn
composer install
cp .env.example .env
php artisan key:generate

# easiest: SQLite for local dev
mkdir -p database
touch database/database.sqlite
# then in .env:
#   DB_CONNECTION=sqlite
#   DB_DATABASE=database/database.sqlite

php artisan migrate --seed
php artisan storage:link

# front-end
npm install
npm run dev      # or: npm run build
php artisan serve
```

Open `http://localhost:8000/installer` to run the installer (only works while
`storage/installed` does not yet exist).

### Working on the neobrutalism theme

The CSS tokens live in `resources/css/app.css` under
`/* ═════ NEOBRUTALISM GLOBAL TOKENS ═════ */`. Run `npm run build` after edits
to regenerate `public/build/assets/app-*.css` — that's the file shared-hosting
users actually load.

The shared `x-button`, `x-card`, `x-input`, etc. components have been re-skinned
once globally, so every admin/auth page inherits the look. You usually don't have
to touch individual page Blade files.

---

## Resetting the installer

```bash
rm storage/installed
# then optionally: rm database/database.sqlite (or drop the MySQL DB)
# open /installer again
```

---

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| `InvalidArgumentException: Please provide a valid cache path.` | A required runtime directory is missing — typically `storage/framework/views/`. Log in as admin → `Maintenance` → **Repair Storage Directories**. If you cannot reach the admin page because of this same error, drop the [emergency `fix.php` script](#emergency-fix-script) into `public/`, hit it once, then delete it. |
| `SQLSTATE[HY000] [2002]` on `php artisan migrate` (Windows dev) | MySQL is not running. Switch to SQLite (see *Local development*) or start your MySQL service. |
| `Vite manifest not found at: …/public/build/manifest.json` | The `public/build/` directory wasn't uploaded. Re-zip including `public/build/**` (it is committed to this repo). |
| `403 Forbidden` on `/admin/emails` | Logged-in user is not an admin (`role != 7`). Promote via the users table or via the maintenance shell. |
| Caches show stale settings after `.env` edit | Log in as admin → `Maintenance` → **Clear All Caches**. |
| Storage uploads return 404 | Log in as admin → `Maintenance` → **Recreate Storage Symlink**. |
| Want to re-run the installer | Delete `storage/installed` and re-open `/installer`. |

### Emergency `fix.php` script

If the app refuses to render at all (e.g. `Please provide a valid cache path`),
upload the following file as `public/fix.php`, visit it once in a browser, then
**delete it** (do not leave it on the server):

```php
<?php
// public/fix.php — visit once, then DELETE THIS FILE

$base = dirname(__DIR__);
$dirs = [
    "$base/storage/framework/cache/data",
    "$base/storage/framework/sessions",
    "$base/storage/framework/testing",
    "$base/storage/framework/views",
    "$base/storage/logs",
    "$base/storage/app/public",
    "$base/bootstrap/cache",
];
foreach ($dirs as $d) {
    if (!is_dir($d)) {
        echo (@mkdir($d, 0775, true) ? "Created: " : "FAILED: ") . $d . "<br>";
    }
    @chmod($d, 0775);
}
foreach (['config.php', 'services.php', 'packages.php', 'routes-v7.php', 'events.php'] as $f) {
    @unlink("$base/bootstrap/cache/$f");
}
echo "<br><b>Done.</b> Refresh the site, then DELETE public/fix.php.";
```

---

## Roadmap / known follow-ups

- HTML purifier on incoming email bodies (defence-in-depth on top of iframe
  sandbox).
- Optional 2FA enforcement for admin role.
- Hashed lock password (currently plain in settings).
- Per-user multi-tenant ownership of email accounts.

---

## License

Proprietary — original CodeCanyon "TMail" license. NeoReborn fork modifies UI &
admin features; license terms inherit from the upstream purchase.
