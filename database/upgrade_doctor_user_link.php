<?php
// Run from CLI after a backup. MariaDB/MySQL schema changes implicitly commit.
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function upgradeDoctorUserLink(PDO $db): void
{
    if ($db->inTransaction()) { throw new RuntimeException('Run this schema upgrade outside a transaction.'); }
    $column = static function (string $table, string $name) use ($db): bool {
        $query = $db->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?');
        $query->execute([$table, $name]);
        return (bool) $query->fetchColumn();
    };
    $lock = 'hospital-doctor-link-'.sha1((string) $db->query('SELECT DATABASE()')->fetchColumn());
    $acquire = $db->prepare('SELECT GET_LOCK(?, 10)');
    $acquire->execute([$lock]);
    if ((int) $acquire->fetchColumn() !== 1) { throw new RuntimeException('Another doctor/account upgrade is running.'); }

    try {
        $legacy = $column('users', 'doctor_id');
        $newLink = $column('doctors', 'user_id');
        // Refuse inconsistent data before making any schema changes or overwriting a link.
        if ($legacy) {
            $invalid = $db->query("SELECT COUNT(*) FROM users u LEFT JOIN doctors d ON d.id=u.doctor_id
                WHERE u.doctor_id IS NOT NULL AND (d.id IS NULL OR u.role <> 'doctor')")->fetchColumn();
            $duplicates = $db->query('SELECT COUNT(*) FROM (SELECT doctor_id FROM users WHERE doctor_id IS NOT NULL GROUP BY doctor_id HAVING COUNT(*)>1) duplicate_links')->fetchColumn();
            if ($invalid || $duplicates) { throw new RuntimeException('Invalid or duplicate legacy doctor links; no links were migrated.'); }
            if ($newLink && $db->query('SELECT COUNT(*) FROM users u JOIN doctors d ON d.id=u.doctor_id
                WHERE (d.user_id IS NOT NULL AND d.user_id<>u.id)
                   OR EXISTS (SELECT 1 FROM doctors other WHERE other.user_id=u.id AND other.id<>d.id)')->fetchColumn()) {
                throw new RuntimeException('Old and new doctor/account links conflict; no links were overwritten.');
            }
        }
        if ($newLink) {
            $invalid = $db->query("SELECT COUNT(*) FROM doctors d LEFT JOIN users u ON u.id=d.user_id
                WHERE d.user_id IS NOT NULL AND (u.id IS NULL OR u.role <> 'doctor')")->fetchColumn();
            $duplicates = $db->query('SELECT COUNT(*) FROM (SELECT user_id FROM doctors WHERE user_id IS NOT NULL GROUP BY user_id HAVING COUNT(*)>1) duplicate_links')->fetchColumn();
            if ($invalid || $duplicates) { throw new RuntimeException('Invalid or duplicate new doctor links; no links were migrated.'); }
        }

        if (!$newLink) { $db->exec('ALTER TABLE doctors ADD COLUMN user_id INT UNSIGNED NULL'); }
        $unique = $db->query("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE()
            AND table_name='doctors' AND index_name='uq_doctors_user' AND column_name='user_id' AND non_unique=0")->fetchColumn();
        if (!$unique) { $db->exec('ALTER TABLE doctors ADD UNIQUE KEY uq_doctors_user (user_id)'); }

        if ($legacy) {
            $db->beginTransaction();
            try {
                $db->exec('UPDATE doctors d JOIN users u ON u.doctor_id=d.id SET d.user_id=u.id WHERE d.user_id IS NULL');
                if ($db->query('SELECT COUNT(*) FROM users u LEFT JOIN doctors d ON d.id=u.doctor_id
                    WHERE u.doctor_id IS NOT NULL AND (d.user_id IS NULL OR d.user_id<>u.id)')->fetchColumn()) {
                    throw new RuntimeException('Doctor/account link verification failed; legacy column was preserved.');
                }
                $db->commit();
            } catch (Throwable $error) {
                if ($db->inTransaction()) { $db->rollBack(); }
                throw $error;
            }
        }

        $foreignKey = $db->query("SELECT COUNT(*) FROM information_schema.key_column_usage WHERE constraint_schema=DATABASE()
            AND table_name='doctors' AND column_name='user_id' AND referenced_table_name='users' AND referenced_column_name='id'")->fetchColumn();
        if (!$foreignKey) {
            $db->exec('ALTER TABLE doctors ADD CONSTRAINT fk_doctors_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE');
        }
        if ($legacy) {
            $constraints = $db->query("SELECT DISTINCT constraint_name FROM information_schema.key_column_usage
                WHERE constraint_schema=DATABASE() AND table_name='users' AND column_name='doctor_id' AND referenced_table_name IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
            $drop = array_map(static fn(string $name): string => 'DROP FOREIGN KEY `'.str_replace('`', '``', $name).'`', $constraints);
            $drop[] = 'DROP COLUMN doctor_id';
            $db->exec('ALTER TABLE users '.implode(', ', $drop));
        }
    } finally {
        $release = $db->prepare('SELECT RELEASE_LOCK(?)');
        $release->execute([$lock]);
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $config = require __DIR__.'/../app/config/db.php';
    $username = getenv('HOSPITAL_MIGRATION_USER');
    $password = getenv('HOSPITAL_MIGRATION_PASSWORD');
    $db = new PDO(getenv('HOSPITAL_MIGRATION_DSN') ?: $config['dsn'],
        $username === false ? $config['username'] : $username,
        $password === false ? $config['password'] : $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    upgradeDoctorUserLink($db);
    echo "Doctor/account links migrated to doctors.user_id; existing records preserved.\n";
}
