<?php

// Tests use an explicitly separate database and never read config/db.php.
$dsn = getenv('HOSPITAL_TEST_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=hospital_test';
if (!preg_match('/^mysql:.*(?:^|;)dbname=(hospital_[a-zA-Z0-9_]*test)(?:;|$)/', $dsn)) {
    throw new \RuntimeException('The test DSN must use MySQL and a database named hospital_*test.');
}
return [
    'class' => \yii\db\Connection::class,
    'dsn' => $dsn,
    'username' => getenv('HOSPITAL_TEST_USER') ?: 'root',
    'password' => getenv('HOSPITAL_TEST_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
];
