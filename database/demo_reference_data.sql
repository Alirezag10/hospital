-- OPTIONAL: import once into a fresh installation after install.sql.
-- No patients, admissions, services provided to patients, or discharges are inserted.
SET NAMES utf8mb4;
INSERT INTO doctors (name, specialty) VALUES ('پزشک نمونه', 'عمومی');
INSERT INTO wards (name, floor, capacity) VALUES ('بخش عمومی', '۱', 10);
INSERT INTO services (title, price, active) VALUES
('ویزیت', 100000, 1),
('آزمایش خون', 150000, 1);
