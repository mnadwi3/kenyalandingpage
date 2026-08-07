-- VaidTrack: add starting price to treatments
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE treatments
  ADD COLUMN price_from VARCHAR(50) NULL AFTER category;

SET FOREIGN_KEY_CHECKS = 1;
