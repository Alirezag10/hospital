-- Additive upgrade for existing installations. All current doctors and wards stay selectable.
ALTER TABLE doctors ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE wards ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1;
