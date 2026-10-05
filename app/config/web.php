<?php
return [
    'id' => 'hospital',
    'name' => 'سامانه بیمارستان',
    'basePath' => dirname(__DIR__),
    'language' => 'fa-IR',
    'bootstrap' => ['log'],
    'aliases' => ['@bower' => '@vendor/bower-asset', '@npm' => '@vendor/npm-asset'],
    'components' => [
        'request' => ['cookieValidationKey' => 'hospital-local-project-7c49a83f2d614b90a5e8f036c1bd7294'],
        'formatter' => ['dateFormat' => 'php:Y-m-d', 'datetimeFormat' => 'php:Y-m-d H:i:s'],
        'errorHandler' => ['errorAction' => 'site/error'],
        'log' => ['targets' => [['class' => \yii\log\FileTarget::class, 'levels' => ['error', 'warning']]]],
        'db' => require __DIR__ . '/db.php',
    ],
];
