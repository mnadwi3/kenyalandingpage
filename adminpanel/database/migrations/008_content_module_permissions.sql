-- Add missing CRUD permissions for Treatments, Hospitals, and Specialties.
-- Super Admin bypasses permission checks in the application layer.
-- Admin receives all permissions; Editor receives view + create + update (no delete).


INSERT INTO permissions (uuid, module, action, name, slug, description)
VALUES
  (UUID(), 'treatments', 'create', 'Create Treatments', 'treatments.create', NULL),
  (UUID(), 'treatments', 'update', 'Update Treatments', 'treatments.update', NULL),
  (UUID(), 'treatments', 'delete', 'Delete Treatments', 'treatments.delete', NULL),
  (UUID(), 'hospitals', 'create', 'Create Hospitals', 'hospitals.create', NULL),
  (UUID(), 'hospitals', 'update', 'Update Hospitals', 'hospitals.update', NULL),
  (UUID(), 'hospitals', 'delete', 'Delete Hospitals', 'hospitals.delete', NULL),
  (UUID(), 'specialties', 'create', 'Create Specialties', 'specialties.create', NULL),
  (UUID(), 'specialties', 'update', 'Update Specialties', 'specialties.update', NULL),
  (UUID(), 'specialties', 'delete', 'Delete Specialties', 'specialties.delete', NULL)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Ensure admin role has every permission
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.slug = 'admin'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Editor: view/create/update for content modules (no delete)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.slug = 'editor'
  AND p.slug IN (
    'dashboard.view',
    'doctors.view', 'doctors.create', 'doctors.update',
    'treatments.view', 'treatments.create', 'treatments.update',
    'hospitals.view', 'hospitals.create', 'hospitals.update',
    'specialties.view', 'specialties.create', 'specialties.update'
  )
ON DUPLICATE KEY UPDATE role_id = role_id;
