-- VaidTrack Phase 6: Specialties module — soft delete support only
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE specialties
  ADD COLUMN deleted_at DATETIME(3) NULL AFTER seo_description,
  ADD KEY idx_specialties_deleted (deleted_at),
  ADD KEY idx_specialties_status_deleted (status, deleted_at);

SET FOREIGN_KEY_CHECKS = 1;
