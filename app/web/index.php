<?php

declare(strict_types=1);

$debugToolbarAllowed = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
$debugToolbarEnabled = $debugToolbarAllowed && ($_COOKIE['yii_debug_toolbar'] ?? '') === '1';

defined('YII_DEBUG') or define('YII_DEBUG', $debugToolbarEnabled);
defined('YII_ENV') or define('YII_ENV', $debugToolbarEnabled ? 'dev' : 'prod');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
