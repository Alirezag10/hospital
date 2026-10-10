-- Fresh installation: import into an empty database only.
SET NAMES utf8mb4;

CREATE TABLE doctors (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doctors_name_specialty (name, specialty)
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
    admission_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    doctor_id INT UNSIGNED NOT NULL,
    ward_id INT UNSIGNED NOT NULL,
    ward VARCHAR(100) NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'admitted',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admissions_patient_id (patient_id),
    KEY idx_admissions_doctor_id (doctor_id),
    KEY idx_admissions_ward_id (ward_id),
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
    PRIMARY KEY (id)
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
    CONSTRAINT fk_discharges_admission
        FOREIGN KEY (admission_id) REFERENCES admissions(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Predefined services used by the operator.
INSERT INTO services (title, price, active) VALUES
('ویزیت', 100000, 1),
('آزمایش خون', 150000, 1),
('تصویربرداری', 300000, 1);
