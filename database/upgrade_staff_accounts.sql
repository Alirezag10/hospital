-- Additive upgrade. Existing patient, admission and billing records are preserved.
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    auth_key VARCHAR(64) NOT NULL,
    role ENUM('admin', 'doctor', 'secretary', 'discharge') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    doctor_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_doctor (doctor_id),
    KEY idx_users_role_active (role, is_active),
    CONSTRAINT fk_users_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
