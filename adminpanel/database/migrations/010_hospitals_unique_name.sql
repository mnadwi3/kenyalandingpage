-- Enforce hospital name uniqueness atomically at the database layer.
-- The application-level check in HospitalService::create() has a
-- check-then-insert race: two near-simultaneous requests can both pass
-- the SELECT before either commits, producing a duplicate under real
-- concurrency (PHP-FPM/Apache workers), even though it can't be
-- reproduced against PHP's single-threaded built-in dev server.
--
-- name_unique_key is NULL for soft-deleted rows so a deleted hospital's
-- name can be reused; MySQL/MariaDB unique indexes allow multiple NULLs.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE hospitals
  ADD COLUMN name_unique_key VARCHAR(200)
    GENERATED ALWAYS AS (CASE WHEN deleted_at IS NULL THEN LOWER(name) ELSE NULL END) STORED
    AFTER name,
  ADD UNIQUE KEY uq_hospitals_name_active (name_unique_key);

SET FOREIGN_KEY_CHECKS = 1;
