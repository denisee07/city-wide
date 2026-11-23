# Deploying to InfinityFree (free PHP hosting)

InfinityFree gives you free PHP 8 hosting with a MySQL database — enough to run
this Laravel app in production for a demo, at no cost.

## 1. Create the account

1. Sign up at <https://app.infinityfree.com> (free).
2. Click **Create Account** → choose any free subdomain (e.g. `citywide.infinityfreeapp.com`).
3. Open the control panel (**Virtualmin / Control Panel**) and note:
   - **FTP hostname**, **FTP username**, **FTP password** (Accounts → FTP Accounts)
   - **MySQL database name / username / password** (MySQL Databases → create one)
   - The **SQL host** (looks like `sqlXXX.infinityfree.com`)

## 2. Prepare the app on this machine

In `.env`, set:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOURNAME.infinityfreeapp.com

DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=ifXXXXXXX_brgy_based     # the database name from the panel
DB_USERNAME=ifXXXXXXX                # the DB username from the panel
DB_PASSWORD=your-db-password

SESSION_DRIVER=file
INSTALL_KEY=some-long-random-string  # unlocks /install/{key} once
```

Keep the `APP_KEY` that is already there. Do **not** commit `.env`.

## 3. Upload

Upload **everything in `brgy-based/`** into `htdocs/` with FileZilla:
- `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `vendor/`, `composer.json`, `.env`, `.htaccess`, `artisan`, …
- **Skip** `node_modules/`, `tests/`, `.git/`.

`vendor/` is thousands of small files — FileZilla's batch upload takes a while;
compress `vendor/` to `vendor.zip`, upload the single zip, and extract it in the
control panel's **File Manager** instead.

## 4. Install (no SSH on free hosting)

Visit once:

```
https://YOURNAME.infinityfreeapp.com/install/YOUR-INSTALL-KEY
```

This runs `php artisan migrate` + `db:seed` over HTTP. When it finishes:

- Sign in with the seeded accounts (`city.admin@barangay.gov` / `password`).
- **Remove `INSTALL_KEY` from `.env`** so the route disappears.

## 5. Notes / limits on free hosting

- No SSH, no `php artisan` — everything runs over the web (hence the installer).
- `vendor/` must be uploaded because composer cannot run on the server.
- Uploads are written to `public/storage/` directly (already configured), so no
  `storage:link` symlink is needed.
- Queue jobs and scheduled tasks do not run; the app already uses the `sync`
  behaviour everywhere it matters.
- Free accounts are suspended after long inactivity — visit the site regularly.
