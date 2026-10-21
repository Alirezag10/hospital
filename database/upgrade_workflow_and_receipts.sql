-- Additive upgrade; no existing admission, bill or account is rewritten.
ALTER TABLE admissions ADD COLUMN IF NOT EXISTS ready_at DATETIME NULL;
ALTER TABLE admissions ADD COLUMN IF NOT EXISTS ready_by INT UNSIGNED NULL;
ALTER TABLE admissions ADD COLUMN IF NOT EXISTS treatment_version INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE admissions ADD INDEX IF NOT EXISTS idx_admissions_patient_status (patient_id,status);
ALTER TABLE admissions ADD INDEX IF NOT EXISTS idx_admissions_ready (status,ready_at,id);
ALTER TABLE discharges ADD COLUMN IF NOT EXISTS receipt_data LONGTEXT NULL;
