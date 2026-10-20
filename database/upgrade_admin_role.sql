-- Upgrade existing accounts without removing users or historical clinical records.
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'secretary', 'discharge') NOT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_role_active (role, is_active);
