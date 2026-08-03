# VaidTrack Admin — Phase 1 (Authentication)

MVC admin panel foundation with secure authentication.

## What Phase 1 includes

- MVC bootstrap (Router, Controllers, Models, Views, Middleware)
- Auth tables: `roles`, `permissions`, `role_permissions`, `users`, `password_reset_tokens`
- Login / Logout
- Forgot + Reset password
- Secure sessions, CSRF, password hashing, RBAC helpers
- Minimal authenticated dashboard placeholder (metrics = Phase 2)

## Setup

1. Create MySQL database and import:

```bash
mysql -u root -p < database/migrations/001_auth_schema.sql
mysql -u root -p < database/seeds/001_admin_user.sql
```

2. Copy `.env.example` to `.env` and set `APP_URL`, DB credentials.

3. Point the web server document root to `public/` (Apache with mod_rewrite, or):

```bash
php -S localhost:8080 -t public
```

4. Open `http://localhost:8080/login`

**Default admin**

- Email: `admin@vaidtrack.com`
- Password: `ChangeMe123!`

Change this password after first login.

## Password reset (local)

Until SMTP settings (Phase 8), reset links are written to `storage/logs/password_resets.log`.  
With `APP_DEBUG=true`, the link is also shown once on the forgot-password page.

## Next phase

Phase 2 — Dashboard (complete).  
Phase 3 — Doctors module.
