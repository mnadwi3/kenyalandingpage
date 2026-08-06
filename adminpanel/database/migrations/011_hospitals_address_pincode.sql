-- Add a full street address (two lines) and pincode so the homepage
-- hospital card can show Address / City / Pincode / Country as its own
-- block, distinct from the existing city/state/country fields.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE hospitals
  ADD COLUMN address_line1 VARCHAR(191) NULL AFTER hospital_type,
  ADD COLUMN address_line2 VARCHAR(191) NULL AFTER address_line1,
  ADD COLUMN pincode VARCHAR(20) NULL AFTER country;

SET FOREIGN_KEY_CHECKS = 1;
