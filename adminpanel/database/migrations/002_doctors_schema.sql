-- VaidTrack Phase 3: Doctors module
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS languages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(10) NOT NULL,
  name VARCHAR(80) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_languages_code (code),
  UNIQUE KEY uq_languages_name (name),
  KEY idx_languages_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS specialties (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid CHAR(36) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  name VARCHAR(150) NOT NULL,
  image VARCHAR(500) NULL,
  description TEXT NULL,
  status ENUM('draft','active','inactive','archived') NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(320) NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_specialties_uuid (uuid),
  UNIQUE KEY uq_specialties_slug (slug),
  UNIQUE KEY uq_specialties_name (name),
  KEY idx_specialties_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS treatments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid CHAR(36) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  name VARCHAR(200) NOT NULL,
  overview MEDIUMTEXT NULL,
  status ENUM('draft','active','inactive','archived') NOT NULL DEFAULT 'active',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(320) NULL,
  featured_image VARCHAR(500) NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_treatments_uuid (uuid),
  UNIQUE KEY uq_treatments_slug (slug),
  KEY idx_treatments_status (status),
  KEY idx_treatments_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hospitals (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid CHAR(36) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  name VARCHAR(200) NOT NULL,
  logo VARCHAR(500) NULL,
  description MEDIUMTEXT NULL,
  status ENUM('draft','active','inactive','archived') NOT NULL DEFAULT 'active',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(320) NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_hospitals_uuid (uuid),
  UNIQUE KEY uq_hospitals_slug (slug),
  KEY idx_hospitals_status (status),
  KEY idx_hospitals_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS doctors (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid CHAR(36) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  name VARCHAR(150) NOT NULL,
  gender ENUM('male','female','other','unspecified') NOT NULL DEFAULT 'unspecified',
  photo VARCHAR(500) NULL,
  qualification VARCHAR(255) NULL,
  designation VARCHAR(150) NULL,
  years_of_experience SMALLINT UNSIGNED NULL,
  expertise TEXT NULL,
  biography MEDIUMTEXT NULL,
  education TEXT NULL,
  awards TEXT NULL,
  memberships TEXT NULL,
  registration_number VARCHAR(100) NULL,
  consultation_fee DECIMAL(12,2) NULL,
  consultation_currency CHAR(3) NULL DEFAULT 'USD',
  video_url VARCHAR(500) NULL,
  status ENUM('draft','active','inactive','archived') NOT NULL DEFAULT 'draft',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(320) NULL,
  deleted_at DATETIME(3) NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  updated_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_doctors_uuid (uuid),
  UNIQUE KEY uq_doctors_slug (slug),
  KEY idx_doctors_status (status),
  KEY idx_doctors_featured (is_featured),
  KEY idx_doctors_name (name),
  KEY idx_doctors_deleted (deleted_at),
  KEY idx_doctors_status_featured (status, is_featured),
  KEY idx_doctors_status_deleted (status, deleted_at),
  FULLTEXT KEY ft_doctors_search (name, designation, qualification, registration_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS doctor_language (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  doctor_id BIGINT UNSIGNED NOT NULL,
  language_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_doctor_language (doctor_id, language_id),
  KEY idx_doctor_language_language (language_id),
  CONSTRAINT fk_doctor_language_doctor FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doctor_language_language FOREIGN KEY (language_id) REFERENCES languages (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS doctor_specialty (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  doctor_id BIGINT UNSIGNED NOT NULL,
  specialty_id BIGINT UNSIGNED NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_doctor_specialty (doctor_id, specialty_id),
  KEY idx_doctor_specialty_specialty (specialty_id),
  CONSTRAINT fk_doctor_specialty_doctor FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doctor_specialty_specialty FOREIGN KEY (specialty_id) REFERENCES specialties (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS doctor_treatment (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  doctor_id BIGINT UNSIGNED NOT NULL,
  treatment_id BIGINT UNSIGNED NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_doctor_treatment (doctor_id, treatment_id),
  KEY idx_doctor_treatment_treatment (treatment_id),
  CONSTRAINT fk_doctor_treatment_doctor FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doctor_treatment_treatment FOREIGN KEY (treatment_id) REFERENCES treatments (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS doctor_hospital (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  doctor_id BIGINT UNSIGNED NOT NULL,
  hospital_id BIGINT UNSIGNED NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (id),
  UNIQUE KEY uq_doctor_hospital (doctor_id, hospital_id),
  KEY idx_doctor_hospital_hospital (hospital_id),
  CONSTRAINT fk_doctor_hospital_doctor FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_doctor_hospital_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
