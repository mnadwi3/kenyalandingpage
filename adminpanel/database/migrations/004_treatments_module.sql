-- VaidTrack Phase 4: Treatments module — expand existing treatments table
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE treatments
  ADD COLUMN category VARCHAR(150) NULL AFTER name,
  ADD COLUMN specialty_id BIGINT UNSIGNED NULL AFTER category,
  ADD COLUMN symptoms MEDIUMTEXT NULL AFTER overview,
  ADD COLUMN when_needed MEDIUMTEXT NULL AFTER symptoms,
  ADD COLUMN procedure_overview MEDIUMTEXT NULL AFTER when_needed,
  ADD COLUMN recovery MEDIUMTEXT NULL AFTER procedure_overview,
  ADD COLUMN why_choose MEDIUMTEXT NULL AFTER recovery,
  ADD COLUMN deleted_at DATETIME(3) NULL AFTER seo_description,
  ADD KEY idx_treatments_category (category),
  ADD KEY idx_treatments_specialty (specialty_id),
  ADD KEY idx_treatments_featured (is_featured),
  ADD KEY idx_treatments_deleted (deleted_at),
  ADD KEY idx_treatments_status_deleted (status, deleted_at),
  ADD CONSTRAINT fk_treatments_specialty
    FOREIGN KEY (specialty_id) REFERENCES specialties (id)
    ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS treatment_hospital (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  treatment_id BIGINT UNSIGNED NOT NULL,
  hospital_id BIGINT UNSIGNED NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_treatment_hospital (treatment_id, hospital_id),
  KEY idx_treatment_hospital_hospital (hospital_id),
  CONSTRAINT fk_treatment_hospital_treatment FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_treatment_hospital_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
