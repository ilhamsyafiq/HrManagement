# Deploying HR Management to Railway

This app is **Laravel 11 + Filament v3 + MySQL + file uploads**. Railway runs it
natively (PHP + MySQL + a persistent volume). Build is via the repo `Dockerfile`.

At runtime **only PHP + MySQL run** — the Vite dev server is NOT used in production
(assets are compiled by `npm run build` during the Docker build).

---

## 1. Push the code to GitHub

```bash
git add -A
git commit -m "Prepare Railway deployment"
git push origin feature/filament-admin-panel
```

(Railway will deploy this branch. You can merge it to `main` later and point Railway at `main`.)

## 2. Create the Railway project + database

1. Go to https://railway.app → **New Project** → **Deploy from GitHub repo** → pick
   `ilhamsyafiq/HrManagement` → branch `feature/filament-admin-panel`.
   Railway detects the `Dockerfile` and builds it.
2. In the same project: **New** → **Database** → **Add MySQL**.

## 3. Set the app service's Variables

Open the **app** service → **Variables** → add (the `${{MySQL.*}}` are Railway
reference variables that auto-wire the MySQL service — adjust `MySQL` if your DB
service has a different name):

```
APP_NAME=HR Management
APP_ENV=production
APP_KEY=base64:04Jg/88sRfXV9M6mzWMAdzyK2OQwVWE7WrBTUT1i2sg=
APP_DEBUG=false
APP_URL=https://REPLACE-with-your-railway-domain

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=sync
MAIL_MAILER=log

HR_PAYROLL_ENABLED=false
```

- **APP_KEY** above was generated for you. To rotate it: `php artisan key:generate --show`.
- After the first deploy, set **APP_URL** to the real domain Railway gives you
  (Settings → Networking → Generate Domain), then redeploy.

## 4. Add a persistent Volume (so uploads survive redeploys)

App service → **Settings** → **Volumes** → **New Volume**, mount path:

```
/app/storage
```

Without this, uploaded reports / signed PDFs / employee documents are lost on every
redeploy (Railway containers are otherwise ephemeral).

> Note: the volume starts empty, so re-run `php artisan storage:link` if the public
> symlink is missing — the container already does this on boot.

## 5. Deploy & first boot

On deploy the container automatically:
`migrate --force` → seed **Super Admin only** (`ProductionSeeder`) → `storage:link`
→ cache config/routes/views → serve on `$PORT`.

Health check path (Settings → Deploy): **`/up`**.

## 6. Log in and secure it

- URL: `https://<your-domain>/panel` (or `/login`).
- **Super Admin:** `superadmin@example.com` / `password`
- **Immediately** change the password: top-right user menu → **Profile** (`/panel/profile`).

---

## Notes / limits
- `php artisan serve` is used as the web server (simple, fine for a small team).
  For higher traffic, switch to FrankenPHP/Octane or nginx+php-fpm later.
- `MAIL_MAILER=log` = no real email. Set real SMTP vars when you want password-reset
  / notification emails to actually send.
- Deploy checklist already baked in: `APP_DEBUG=false`, `APP_ENV=production`,
  `SESSION_SECURE_COOKIE=true`, trusted proxies (for HTTPS behind Railway's LB).
- Self-registration route is still open (`/register`); disable it in `routes/auth.php`
  if you don't want public sign-ups on the live site.
