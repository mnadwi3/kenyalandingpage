-- VaidTrack: create/reset the first administrator account.
-- Email: admin@vaidtrack.com | Password: Admin@123
-- Hash generated with the app's own method: password_hash($plain, PASSWORD_DEFAULT)
-- Safe to re-run: upserts by email's unique key (uq_users_email).
--
-- Self-contained: ensures the super_admin role exists in this same script
-- before selecting it, rather than assuming 001_admin_user.sql already ran.
-- INSERT ... SELECT ... WHERE (no match) silently inserts zero rows with no
-- error, so this ordering matters — see the incident this fixed.

INSERT INTO roles (uuid, name, slug, description, is_system, status)
VALUES (UUID(), 'Super Admin', 'super_admin', 'Full system access', 1, 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO users (uuid, role_id, name, email, password_hash, status, password_changed_at)
SELECT
  UUID(),
  r.id,
  'Admin',
  'admin@vaidtrack.com',
  '$2y$10$gslqCuzry548IsfklvOoXury2zlzCYeSWsZf5JjE9IIPP0nRv0uJ2',
  'active',
  NOW(3)
FROM roles r
WHERE r.slug = 'super_admin'
LIMIT 1
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  password_hash = VALUES(password_hash),
  status = 'active',
  password_changed_at = NOW(3);
