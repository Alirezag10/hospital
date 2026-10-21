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
