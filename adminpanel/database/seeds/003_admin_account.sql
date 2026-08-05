-- VaidTrack: create/reset the first administrator account.
-- Email: admin@vaidtrack.com | Password: Admin@123
-- Hash generated with the app's own method: password_hash($plain, PASSWORD_DEFAULT)
-- Safe to re-run: upserts by email's unique key (uq_users_email).

USE vaidtrack;

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
