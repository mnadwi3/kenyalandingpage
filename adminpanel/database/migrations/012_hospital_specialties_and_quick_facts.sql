-- VaidTrack: Hospital quick facts, hero alt text, short description, website,
-- and a direct hospital <-> specialty relation (independent of doctor assignments).
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE hospitals
  ADD COLUMN website VARCHAR(255) NULL AFTER hospital_type,
  ADD COLUMN short_description VARCHAR(320) NULL AFTER description,
  ADD COLUMN hero_image_alt VARCHAR(255) NULL AFTER cover_image,
  ADD COLUMN icu_beds INT UNSIGNED NULL AFTER number_of_beds,
  ADD COLUMN operation_theatres INT UNSIGNED NULL AFTER icu_beds,
  ADD COLUMN international_patients_per_year INT UNSIGNED NULL AFTER operation_theatres,
  ADD COLUMN airport_distance VARCHAR(50) NULL AFTER international_patients_per_year;

CREATE TABLE IF NOT EXISTS hospital_specialty (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  hospital_id BIGINT UNSIGNED NOT NULL,
  specialty_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_hospital_specialty (hospital_id, specialty_id),
  KEY idx_hospital_specialty_specialty (specialty_id),
  CONSTRAINT fk_hospital_specialty_hospital
    FOREIGN KEY (hospital_id) REFERENCES hospitals (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_hospital_specialty_specialty
    FOREIGN KEY (specialty_id) REFERENCES specialties (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
