-- VaidTrack Phase 9: Content sync module — testimonials, FAQs, site settings (hero)
-- Powers the public JSON API that syncs the admin panel with the static frontend.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS testimonials (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid CHAR(36) NOT NULL,
  patient_name VARCHAR(150) NOT NULL,
  treatment_name VARCHAR(150) NULL,
  quote MEDIUMTEXT NULL,
  youtube_id VARCHAR(50) NULL,
  thumbnail VARCHAR(500) NULL,
  status ENUM('draft','active','inactive','archived') NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  deleted_at DATETIME(3) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_testimonials_uuid (uuid),
  KEY idx_testimonials_status (status),
  KEY idx_testimonials_sort (sort_order),
  KEY idx_testimonials_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faqs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid CHAR(36) NOT NULL,
  question VARCHAR(500) NOT NULL,
  answer MEDIUMTEXT NULL,
  status ENUM('draft','active','inactive','archived') NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  deleted_at DATETIME(3) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_faqs_uuid (uuid),
  KEY idx_faqs_status (status),
  KEY idx_faqs_sort (sort_order),
  KEY idx_faqs_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Simple key/value store for singleton content blocks (hero, etc.)
CREATE TABLE IF NOT EXISTS site_settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(150) NOT NULL,
  setting_value MEDIUMTEXT NULL,
  updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_site_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO site_settings (setting_key, setting_value) VALUES
  ('hero', JSON_OBJECT(
    'eyebrow', 'India''s Trusted Cancer Care Network',
    'headline', 'World-Class Cancer Treatment in India',
    'subheadline', 'Free second opinion from top Indian oncologists. Transparent pricing, verified hospitals, and dedicated care coordination from day one.',
    'trust_points', JSON_ARRAY('500+ Successful Treatments', 'JCI Accredited Hospitals', '24/7 Patient Support'),
    'image', ''
  ))
ON DUPLICATE KEY UPDATE setting_key = setting_key;

INSERT INTO permissions (uuid, module, action, name, slug, description)
VALUES
  (UUID(), 'testimonials', 'view', 'View Testimonials', 'testimonials.view', NULL),
  (UUID(), 'testimonials', 'create', 'Create Testimonials', 'testimonials.create', NULL),
  (UUID(), 'testimonials', 'update', 'Update Testimonials', 'testimonials.update', NULL),
  (UUID(), 'testimonials', 'delete', 'Delete Testimonials', 'testimonials.delete', NULL),
  (UUID(), 'faqs', 'view', 'View FAQs', 'faqs.view', NULL),
  (UUID(), 'faqs', 'create', 'Create FAQs', 'faqs.create', NULL),
  (UUID(), 'faqs', 'update', 'Update FAQs', 'faqs.update', NULL),
  (UUID(), 'faqs', 'delete', 'Delete FAQs', 'faqs.delete', NULL),
  (UUID(), 'settings', 'view', 'View Site Settings', 'settings.view', NULL),
  (UUID(), 'settings', 'update', 'Update Site Settings', 'settings.update', NULL)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.slug = 'admin'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.slug = 'editor'
  AND p.slug IN (
    'testimonials.view', 'testimonials.create', 'testimonials.update',
    'faqs.view', 'faqs.create', 'faqs.update',
    'settings.view'
  )
ON DUPLICATE KEY UPDATE role_id = role_id;

SET FOREIGN_KEY_CHECKS = 1;
