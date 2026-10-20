<?php
// Local integration server only; this file is outside the public web directory.
$dsn = getenv('HOSPITAL_TEST_DSN');
if (!$dsn || !preg_match('/dbname=[a-zA-Z0-9_]+_test(?:;|$)/', $dsn)) {
    http_response_code(500);
    exit('An isolated _test database is required.');
}
$web = realpath(__DIR__ . '/../app/web');
$file = realpath($web . rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
if ($file && str_starts_with($file, $web . DIRECTORY_SEPARATOR) && is_file($file)
    && strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') { return false; }
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'test');
require __DIR__ . '/../app/vendor/autoload.php';
require __DIR__ . '/../app/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/../app/config/web.php';
$config['components']['db'] = [
    'class' => yii\db\Connection::class, 'dsn' => $dsn,
    'username' => getenv('HOSPITAL_TEST_USER') ?: 'root', 'password' => getenv('HOSPITAL_TEST_PASSWORD') ?: '', 'charset' => 'utf8mb4',
];
$config['components']['request']['scriptFile'] = $web . '/index.php';
$config['components']['request']['scriptUrl'] = '/index.php';
$config['components']['request']['cookieValidationKey'] = 'hospital-test-cookie-validation-only';
$config['components']['session']['name'] = 'HOSPITAL_TEST_SESSION';
$config['components']['user']['identityCookie']['name'] = '_hospital_test_identity';
$config['runtimePath'] = dirname(__DIR__) . '/app/runtime/staff-tests';
(new yii\web\Application($config))->run();
