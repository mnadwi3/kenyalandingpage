-- Rename Experience → Expertise (Doctors module field rename only)
USE vaidtrack;

ALTER TABLE doctors
  CHANGE COLUMN experience_summary expertise TEXT NULL;
