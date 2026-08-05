-- Remove unused Enquiries and Settings permissions
SET NAMES utf8mb4;

DELETE rp FROM role_permissions rp
INNER JOIN permissions p ON p.id = rp.permission_id
WHERE p.slug IN ('enquiries.view', 'settings.update');

DELETE FROM permissions
WHERE slug IN ('enquiries.view', 'settings.update');
