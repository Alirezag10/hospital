-- Fresh installation: import into an empty database only.
SET NAMES utf8mb4;

CREATE TABLE doctors (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    user_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doctors_name_specialty (name, specialty),
    UNIQUE KEY uq_doctors_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO doctors (name, specialty, mobile) VALUES
('دکتر علی رضایی', 'قلب و عروق', '09122000001'),
('دکتر مریم حسینی', 'داخلی', '09122000002'),
('دکتر امیر محمدی', 'ارتوپدی', '09122000003'),
('دکتر نرگس کریمی', 'اطفال', '09122000004'),
('دکتر رضا احمدی', 'جراحی عمومی', '09122000005');

CREATE TABLE wards (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    floor VARCHAR(50) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_wards_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `patients` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `national_code` VARCHAR(20) NOT NULL,
    `mobile` VARCHAR(20) NOT NULL,
    `birth_date` DATE NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_patients_national_code` (`national_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admissions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    patient_id INT UNSIGNED NOT NULL,
    admission_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    doctor_id INT UNSIGNED NOT NULL,
    ward_id INT UNSIGNED NOT NULL,
    ward VARCHAR(100) NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'admitted',
    created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admissions_patient_id (patient_id),
    KEY idx_admissions_doctor_id (doctor_id),
    KEY idx_admissions_ward_id (ward_id),
    KEY idx_admissions_status_id (status, id),
    KEY idx_admissions_admission_date (admission_date),
    CONSTRAINT fk_admissions_patient
        FOREIGN KEY (patient_id) REFERENCES patients(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_admissions_doctor
        FOREIGN KEY (doctor_id) REFERENCES doctors(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_admissions_ward
        FOREIGN KEY (ward_id) REFERENCES wards(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    price INT UNSIGNED NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_services_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admission_services (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    admission_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admission_services_admission_id (admission_id),
    KEY idx_admission_services_service_id (service_id),
    CONSTRAINT fk_admission_services_admission
        FOREIGN KEY (admission_id) REFERENCES admissions(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_admission_services_service
        FOREIGN KEY (service_id) REFERENCES services(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE discharges (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    admission_id INT UNSIGNED NOT NULL,
    discharge_date DATETIME NOT NULL,
    description TEXT NULL,
    total_amount BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_discharges_admission_id (admission_id),
    KEY idx_discharges_discharge_date (discharge_date),
    CONSTRAINT fk_discharges_admission
        FOREIGN KEY (admission_id) REFERENCES admissions(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Predefined services used by the operator.
INSERT INTO services (title, price, active) VALUES
('ویزیت', 100000, 1),
('آزمایش خون', 150000, 1),
('تصویربرداری', 300000, 1);


-- Additive upgrade. Existing patient, admission and billing records are preserved.
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    auth_key VARCHAR(64) NOT NULL,
    role ENUM('admin', 'doctor', 'secretary', 'discharge') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    KEY idx_users_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE doctors ADD CONSTRAINT fk_doctors_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS prescriptions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    admission_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    diagnosis TEXT NOT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_prescriptions_admission (admission_id),
    KEY idx_prescriptions_doctor (doctor_id),
    KEY idx_prescriptions_user (user_id),
    CONSTRAINT fk_prescriptions_admission FOREIGN KEY (admission_id) REFERENCES admissions(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_prescriptions_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_prescriptions_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prescription_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    prescription_id INT UNSIGNED NOT NULL,
    kind ENUM('medicine', 'supply') NOT NULL,
    title VARCHAR(150) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    dosage VARCHAR(100) NULL,
    instructions VARCHAR(500) NULL,
    unit_price INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_prescription_items_prescription (prescription_id),
    CONSTRAINT fk_prescription_items_prescription FOREIGN KEY (prescription_id) REFERENCES prescriptions(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Additive upgrade: existing clinical values and finalized bills are preserved.
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS medical_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    kind ENUM('medicine','supply') NOT NULL,
    title VARCHAR(150) NOT NULL,
    unit_price INT UNSIGNED NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_medical_items_kind_title (kind,title),
    KEY idx_medical_items_active_kind (active,kind)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE prescription_items ADD COLUMN IF NOT EXISTS medical_item_id INT UNSIGNED NULL;
ALTER TABLE prescription_items ADD INDEX IF NOT EXISTS idx_items_catalog (medical_item_id);
ALTER TABLE prescriptions ADD COLUMN IF NOT EXISTS revision INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE admission_services ADD COLUMN IF NOT EXISTS revision INT UNSIGNED NOT NULL DEFAULT 1;
CREATE TABLE IF NOT EXISTS record_changes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    admission_id INT UNSIGNED NOT NULL,
    entity_type ENUM('prescription','admission_service') NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    actor_id INT UNSIGNED NOT NULL,
    reason VARCHAR(500) NOT NULL,
    before_data LONGTEXT NOT NULL,
    after_data LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_record_changes_admission (admission_id,created_at,id),
    CONSTRAINT fk_changes_admission FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE RESTRICT,
    CONSTRAINT fk_changes_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Use the latest existing snapshot only to initialize each reference item.
INSERT IGNORE INTO medical_items (kind,title,unit_price)
SELECT i.kind,i.title,i.unit_price FROM prescription_items i
INNER JOIN (SELECT MAX(id) AS id FROM prescription_items GROUP BY kind,title) latest ON latest.id=i.id;
INSERT IGNORE INTO medical_items (kind,title,unit_price) VALUES
('medicine','استامینوفن',15000),('medicine','سرم',45000),
('supply','گاز استریل',10000),('supply','ست سرم',25000);
UPDATE prescription_items i INNER JOIN medical_items m ON m.kind=i.kind AND m.title=i.title
SET i.medical_item_id=m.id WHERE i.medical_item_id IS NULL;
SET @catalog_fk_sql = IF(
    EXISTS(SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='prescription_items' AND CONSTRAINT_NAME='fk_items_catalog'),
    'SELECT 1',
    'ALTER TABLE prescription_items ADD CONSTRAINT fk_items_catalog FOREIGN KEY (medical_item_id) REFERENCES medical_items(id) ON DELETE RESTRICT');
PREPARE catalog_fk_stmt FROM @catalog_fk_sql;
EXECUTE catalog_fk_stmt;
DEALLOCATE PREPARE catalog_fk_stmt;
-- Additive upgrade; no existing admission, bill or account is rewritten.
ALTER TABLE admissions ADD COLUMN IF NOT EXISTS ready_at DATETIME NULL;
ALTER TABLE admissions ADD COLUMN IF NOT EXISTS ready_by INT UNSIGNED NULL;
ALTER TABLE admissions ADD COLUMN IF NOT EXISTS treatment_version INT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE admissions ADD INDEX IF NOT EXISTS idx_admissions_patient_status (patient_id,status);
ALTER TABLE admissions ADD INDEX IF NOT EXISTS idx_admissions_ready (status,ready_at,id);
ALTER TABLE discharges ADD COLUMN IF NOT EXISTS receipt_data LONGTEXT NULL;
