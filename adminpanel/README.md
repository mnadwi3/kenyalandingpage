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
