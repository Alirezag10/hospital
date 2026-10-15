-- Existing legacy records with a zero timestamp have no trustworthy admission time.
-- Run on the existing database; the statements can be repeated safely.
ALTER TABLE admissions
    MODIFY admission_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE admissions SET admission_date = NULL
WHERE CAST(admission_date AS CHAR) LIKE '0000-00-00%';

UPDATE admissions SET created_at = NULL
WHERE CAST(created_at AS CHAR) LIKE '0000-00-00%';
