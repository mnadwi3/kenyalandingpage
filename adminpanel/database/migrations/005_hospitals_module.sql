-- VaidTrack Phase 5: Hospitals module — expand existing hospitals table
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE hospitals
  ADD COLUMN cover_image VARCHAR(500) NULL AFTER logo,
  ADD COLUMN established_year SMALLINT UNSIGNED NULL AFTER description,
  ADD COLUMN number_of_beds INT UNSIGNED NULL AFTER established_year,
  ADD COLUMN hospital_type VARCHAR(100) NULL AFTER number_of_beds,
  ADD COLUMN city VARCHAR(120) NULL AFTER hospital_type,
  ADD COLUMN state VARCHAR(120) NULL AFTER city,
  ADD COLUMN country VARCHAR(120) NULL AFTER state,
  ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER is_featured,
  ADD COLUMN deleted_at DATETIME(3) NULL AFTER seo_description,
  ADD KEY idx_hospitals_featured (is_featured),
  ADD KEY idx_hospitals_type (hospital_type),
  ADD KEY idx_hospitals_city (city),
  ADD KEY idx_hospitals_country (country),
  ADD KEY idx_hospitals_deleted (deleted_at),
  ADD KEY idx_hospitals_status_deleted (status, deleted_at);

CREATE TABLE IF NOT EXISTS hospital_accreditation (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  hospital_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(30) NOT NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_hospital_accreditation (hospital_id, code),
  KEY idx_hospital_accreditation_code (code),
  CONSTRAINT fk_hospital_accreditation_hospital
    FOREIGN KEY (hospital_id) REFERENCES hospitals (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hospital_international_service (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  hospital_id BIGINT UNSIGNED NOT NULL,
  service_code VARCHAR(50) NOT NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_hospital_intl_service (hospital_id, service_code),
  KEY idx_hospital_intl_service_code (service_code),
  CONSTRAINT fk_hospital_intl_service_hospital
    FOREIGN KEY (hospital_id) REFERENCES hospitals (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preserve / ensure treatment ↔ hospital junction (may already exist from Phase 4)
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
