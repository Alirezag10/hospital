<?php
declare(strict_types=1);

$dsn = getenv('HOSPITAL_TEST_DSN') ?: '';
if (!preg_match('/dbname=[a-zA-Z0-9_]+_test(?:;|$)/', $dsn)) { throw new RuntimeException('Use an isolated _test database.'); }
$db = new PDO($dsn, getenv('HOSPITAL_TEST_USER') ?: 'root', getenv('HOSPITAL_TEST_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
if ((int) $db->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()')->fetchColumn() !== 0) {
    throw new RuntimeException('Test database must be empty. No data was changed.');
}
require __DIR__.'/../database/upgrade_doctor_user_link.php';
function migrationCheck(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException('FAIL: '.$message); }
    echo 'PASS ', $message, PHP_EOL;
}
$db->exec("CREATE TABLE doctors (id INT UNSIGNED PRIMARY KEY, name VARCHAR(100) NOT NULL) ENGINE=InnoDB;
    CREATE TABLE users (id INT UNSIGNED PRIMARY KEY, username VARCHAR(50), password_hash VARCHAR(255), auth_key VARCHAR(64),
        role VARCHAR(20), doctor_id INT UNSIGNED NULL, UNIQUE KEY uq_users_doctor(doctor_id),
        CONSTRAINT fk_users_doctor FOREIGN KEY(doctor_id) REFERENCES doctors(id)) ENGINE=InnoDB;
    CREATE TABLE history (id INT PRIMARY KEY, doctor_id INT UNSIGNED, user_id INT UNSIGNED,
        FOREIGN KEY(doctor_id) REFERENCES doctors(id), FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB;
    INSERT INTO doctors VALUES (101,'Physician A'),(102,'Physician B'),(103,'No account');
    INSERT INTO users VALUES (1,'admin','hash-a','key-a','admin',NULL),(2,'doctor-a','hash-b','key-b','doctor',101),
        (3,'doctor-b','hash-c','key-c','doctor',102),(4,'secretary','hash-d','key-d','secretary',NULL),(5,'discharge','hash-e','key-e','discharge',NULL);
    INSERT INTO history VALUES (1,101,2);");
$usersBefore = $db->query('SELECT id,username,password_hash,auth_key,role FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$doctorsBefore = $db->query('SELECT id,name FROM doctors ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$historyBefore = $db->query('SELECT * FROM history')->fetchAll(PDO::FETCH_ASSOC);

$db->exec('UPDATE users SET doctor_id=103 WHERE id=1');
try { upgradeDoctorUserLink($db); throw new RuntimeException('Invalid role link was accepted.'); }
catch (RuntimeException $error) { migrationCheck(str_contains($error->getMessage(), 'Invalid or duplicate legacy'), 'invalid legacy role is rejected'); }
migrationCheck((int) $db->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='doctors' AND column_name='user_id'")->fetchColumn() === 0,
    'failed preflight changes no schema');
$db->exec('UPDATE users SET doctor_id=NULL WHERE id=1');
upgradeDoctorUserLink($db);
migrationCheck($db->query('SELECT id,username,password_hash,auth_key,role FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC) === $usersBefore,
    'all accounts, password hashes, auth keys and roles are preserved');
migrationCheck($db->query('SELECT id,name FROM doctors ORDER BY id')->fetchAll(PDO::FETCH_ASSOC) === $doctorsBefore,
    'all physician IDs and profile data are preserved');
migrationCheck($db->query('SELECT * FROM history')->fetchAll(PDO::FETCH_ASSOC) === $historyBefore, 'historical author and physician references are unchanged');
migrationCheck((int) $db->query('SELECT user_id FROM doctors WHERE id=101')->fetchColumn() === 2
    && (int) $db->query('SELECT user_id FROM doctors WHERE id=102')->fetchColumn() === 3, 'both existing doctor/account links are transferred');
migrationCheck($db->query('SELECT user_id FROM doctors WHERE id=103')->fetchColumn() === null, 'physician without an account stays unlinked');
migrationCheck((int) $db->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='users' AND column_name='doctor_id'")->fetchColumn() === 0,
    'legacy users.doctor_id column is removed');
upgradeDoctorUserLink($db);
migrationCheck((int) $db->query('SELECT COUNT(*) FROM doctors WHERE user_id IS NOT NULL')->fetchColumn() === 2, 'repeated upgrade preserves all links');
foreach (['2' => 'duplicate account', '999999' => 'missing account'] as $value => $message) {
    try { $db->exec('UPDATE doctors SET user_id='.$value.' WHERE id=103'); throw new RuntimeException('Constraint did not reject '.$message); }
    catch (PDOException $error) { migrationCheck($error->getCode() === '23000', 'database rejects '.$message); }
}

// An interrupted/partially prepared deployment must not silently overwrite conflicting links.
$db->exec('ALTER TABLE users ADD COLUMN doctor_id INT UNSIGNED NULL;
    UPDATE users SET doctor_id=101 WHERE id=2;
    UPDATE users SET doctor_id=102 WHERE id=3;
    UPDATE doctors SET user_id=NULL WHERE id=102;
    UPDATE doctors SET user_id=3 WHERE id=101;');
try { upgradeDoctorUserLink($db); throw new RuntimeException('Conflicting links were accepted.'); }
catch (RuntimeException $error) { migrationCheck(str_contains($error->getMessage(), 'links conflict'), 'conflicting partial migration is rejected'); }
migrationCheck((int) $db->query('SELECT doctor_id FROM users WHERE id=2')->fetchColumn() === 101
    && (int) $db->query('SELECT user_id FROM doctors WHERE id=101')->fetchColumn() === 3, 'conflict leaves both link representations untouched');
$db->exec('UPDATE doctors SET user_id=2 WHERE id=101; UPDATE doctors SET user_id=3 WHERE id=102');
upgradeDoctorUserLink($db);
migrationCheck((int) $db->query('SELECT COUNT(*) FROM doctors WHERE user_id IS NOT NULL')->fetchColumn() === 2, 'corrected partial migration can be resumed');
echo "SUCCESS: doctor/account migration checks passed.\n";
