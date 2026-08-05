INSERT INTO languages (code, name, status) VALUES
  ('en', 'English', 'active'),
  ('hi', 'Hindi', 'active'),
  ('ar', 'Arabic', 'active'),
  ('fr', 'French', 'active'),
  ('sw', 'Swahili', 'active'),
  ('bn', 'Bengali', 'active'),
  ('ru', 'Russian', 'active'),
  ('es', 'Spanish', 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO specialties (uuid, slug, name, status) VALUES
  (UUID(), 'surgical-oncology', 'Surgical Oncology', 'active'),
  (UUID(), 'medical-oncology', 'Medical Oncology', 'active'),
  (UUID(), 'radiation-oncology', 'Radiation Oncology', 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO treatments (uuid, slug, name, status) VALUES
  (UUID(), 'breast-cancer', 'Breast Cancer Treatment', 'active'),
  (UUID(), 'lung-cancer', 'Lung Cancer Treatment', 'active'),
  (UUID(), 'prostate-cancer', 'Prostate Cancer Treatment', 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO hospitals (uuid, slug, name, status) VALUES
  (UUID(), 'apollo-delhi', 'Apollo Hospitals Delhi', 'active'),
  (UUID(), 'fortis-gurugram', 'Fortis Memorial Gurugram', 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name);
