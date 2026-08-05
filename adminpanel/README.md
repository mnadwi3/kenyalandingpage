# VaidTrack Admin — Local Development

Vanilla PHP 8 MVC admin panel (Authentication + Dashboard). No Composer.

## Stack detected

| Layer | Choice |
|--------|--------|
| Runtime | PHP 8.3 built-in server (`php -S`) |
| Database | MariaDB / MySQL |
| Front controller | `public/index.php` + `public/router.php` |
| Config | `.env` |

Apache/XAMPP/Laragon also work if the vhost document root is `adminpanel/public`.

## Quick start

```bash
cd adminpanel
sudo service mariadb start   # if needed
./scripts/start-dev.sh
```

Open **http://127.0.0.1:8080/login**

**Default admin**

- Email: `admin@vaidtrack.com`
- Password: `ChangeMe123!`

## First-time database setup

```bash
cd adminpanel
mysql -u root -p < database/migrations/001_auth_schema.sql
mysql -u root -p < database/seeds/001_admin_user.sql
cp -n .env.example .env
# Edit DB_* and APP_URL=http://127.0.0.1:8080
```

Default local `.env` credentials used in this environment:

- DB name: `vaidtrack`
- DB user / pass: `vaidtrack` / `vaidtrack`

## Required PHP extensions

`pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `curl`, `zip`, `xml`, `session`, `json`

## Writable paths

- `storage/logs`, `storage/cache`
- `uploads/`, `public/uploads/`, `public/uploads/doctors/`

## Password reset (local)

Reset links go to `storage/logs/password_resets.log`. With `APP_DEBUG=true`, the link is also shown once on the forgot-password page.

## Production deployment (Hostinger, shared document root)

This app is deployed as a subfolder of the main site's document root
(`https://vaidtrack.com/adminpanel`), reached via the two-tier rewrite chain
`adminpanel/.htaccess` → `adminpanel/public/.htaccess` → `public/index.php`.
The vhost/domain document root stays the site root (the folder containing
`index.html`); it must **not** point directly at `adminpanel/public`.

`App\Core\App::loadEnv()` only reads `adminpanel/.env` — `.env.example` and
`.env.production.example` are never loaded at runtime. If `.env` is missing,
every config value silently falls back to its `env()` default, `APP_URL`
resolves to `''`, and `Router::dispatch()` can no longer strip the
`/adminpanel` prefix from incoming requests — every route then 404s. A real
`.env` file in `adminpanel/` (not `adminpanel/public/`) is mandatory.

**Everything below is already generated in `adminpanel/.env.production.example`**
— `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` (set to
`https://vaidtrack.com/adminpanel`), a random 64-char `APP_KEY`, `DB_HOST`/
`DB_PORT` (Hostinger shared-MySQL defaults), and `SESSION_NAME`/
`SESSION_LIFETIME`. None of these require manual editing.

**Remaining manual steps** (require Hostinger account access this environment
does not have):

1. In hPanel → Databases, create (or locate) the MySQL database and user for
   this app, and note the database name, username, and password.
2. Copy `adminpanel/.env.production.example` to `adminpanel/.env` on the
   server, filling in `DB_NAME`, `DB_USER`, `DB_PASS` from step 1. This is the
   only file content that needs editing — everything else in it is final.
3. Run all migrations against the production database, in order:
   ```bash
   cd adminpanel/database/migrations
   for f in 001_auth_schema.sql 002_doctors_schema.sql \
            003_rename_experience_to_expertise.sql 004_treatments_module.sql \
            005_hospitals_module.sql 006_specialties_module.sql \
            007_remove_enquiries_settings_permissions.sql \
            008_content_module_permissions.sql 009_content_sync_module.sql; do
     mysql -u DB_USER -p DB_NAME < "$f"
   done
   mysql -u DB_USER -p DB_NAME < ../seeds/001_admin_user.sql
   mysql -u DB_USER -p DB_NAME < ../seeds/002_doctors_related.sql
   ```
   (via Hostinger's phpMyAdmin import UI or SSH, whichever is available.)
4. Log in at `https://vaidtrack.com/adminpanel/login` with the seeded
   `admin@vaidtrack.com` / `ChangeMe123!` and change the password immediately.
5. Confirm `adminpanel/public/uploads/{doctors,hospitals,treatments,site}` are
   writable by the PHP process (needed for image uploads).
