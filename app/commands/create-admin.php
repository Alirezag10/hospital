<?php
// One-time local setup; never expose this file under app/web.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

new yii\console\Application([
    'id' => 'hospital-admin-setup', 'basePath' => dirname(__DIR__),
    'components' => ['db' => require dirname(__DIR__) . '/config/db.php'],
]);
if (app\models\User::find()->where(['role' => app\models\User::ADMIN, 'is_active' => 1])->exists()) {
    echo "An active administrator already exists. No account changed.\n";
    exit(0);
}
$password = Yii::$app->security->generateRandomString(20);
$form = new app\models\UserForm([
    'username' => $argv[1] ?? 'admin', 'full_name' => 'مدیر سیستم',
    'role' => app\models\User::ADMIN, 'password' => $password, 'password_repeat' => $password,
]);
if ($form->save() === null) {
    fwrite(STDERR, json_encode($form->getErrors(), JSON_UNESCAPED_UNICODE) . PHP_EOL);
    exit(1);
}
$path = dirname(__DIR__) . '/runtime/admin-access.txt';
file_put_contents($path, "Login: http://localhost:8080/site/login\nUsername: {$form->username}\nPassword: {$password}\n");
@chmod($path, 0600);
echo "Administrator created. Credentials saved locally in app/runtime/admin-access.txt\n";
