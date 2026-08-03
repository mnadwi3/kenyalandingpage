-- VaidTrack Phase 1 seed: roles, baseline permissions, default super admin
-- Default login: admin@vaidtrack.com / ChangeMe123!
-- CHANGE THIS PASSWORD immediately after first login.

USE vaidtrack;

INSERT INTO roles (uuid, name, slug, description, is_system, status)
VALUES
  (UUID(), 'Super Admin', 'super_admin', 'Full system access', 1, 'active'),
  (UUID(), 'Admin', 'admin', 'Operational administrator', 1, 'active'),
  (UUID(), 'Editor', 'editor', 'Content editor', 1, 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO permissions (uuid, module, action, name, slug, description)
VALUES
  (UUID(), 'dashboard', 'view', 'View Dashboard', 'dashboard.view', NULL),
  (UUID(), 'doctors', 'view', 'View Doctors', 'doctors.view', NULL),
  (UUID(), 'doctors', 'create', 'Create Doctors', 'doctors.create', NULL),
  (UUID(), 'doctors', 'update', 'Update Doctors', 'doctors.update', NULL),
  (UUID(), 'doctors', 'delete', 'Delete Doctors', 'doctors.delete', NULL),
  (UUID(), 'treatments', 'view', 'View Treatments', 'treatments.view', NULL),
  (UUID(), 'hospitals', 'view', 'View Hospitals', 'hospitals.view', NULL),
  (UUID(), 'specialties', 'view', 'View Specialties', 'specialties.view', NULL),
  (UUID(), 'enquiries', 'view', 'View Enquiries', 'enquiries.view', NULL),
  (UUID(), 'settings', 'update', 'Update Settings', 'settings.update', NULL)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Grant all permissions to admin role (super_admin bypasses checks in app)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.slug = 'admin'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Grant content permissions to editor
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.slug = 'editor'
  AND p.slug IN ('dashboard.view', 'doctors.view', 'doctors.create', 'doctors.update', 'treatments.view', 'hospitals.view', 'specialties.view')
ON DUPLICATE KEY UPDATE role_id = role_id;

-- password = ChangeMe123!  (bcrypt)
INSERT INTO users (uuid, role_id, name, email, password_hash, status, password_changed_at)
SELECT
  UUID(),
  r.id,
  'Super Admin',
  'admin@vaidtrack.com',
  '$2b$10$i5jr3CWUFKED7leiz9kEgOeKZNuUP4lqteKS10.1db6Hogn55uUni',
  'active',
  NOW(3)
FROM roles r
WHERE r.slug = 'super_admin'
LIMIT 1
ON DUPLICATE KEY UPDATE email = email;
