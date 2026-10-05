<?php

// Import schema only into an empty, explicitly named test database.
$root = dirname(__DIR__);
$config = require $root . '/app/config/test_db.php';
$pdo = new PDO($config['dsn'], $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
if ($tables !== []) {
    fwrite(STDERR, "Test database is not empty; schema was not imported.\n");
    exit(1);
}
$pdo->exec(file_get_contents($root . '/database/install.sql'));
echo "Hospital test schema imported successfully.\n";
