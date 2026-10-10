<?php
$config = [
    'id' => 'hospital',
    'name' => 'سامانه بیمارستان',
    'basePath' => dirname(__DIR__),
    'language' => 'fa-IR',
    'bootstrap' => ['log'],
    'container' => [
        'definitions' => [
            \yii\widgets\ActiveForm::class => [
                'validateOnBlur' => false,
                'validateOnChange' => false,
                'validateOnType' => false,
                'validateOnSubmit' => true,
            ],
        ],
    ],
    'aliases' => ['@bower' => '@vendor/bower-asset', '@npm' => '@vendor/npm-asset'],
    'components' => [
        'assetManager' => ['appendTimestamp' => true],
        'request' => ['cookieValidationKey' => 'hospital-local-project-7c49a83f2d614b90a5e8f036c1bd7294'],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
        'formatter' => ['dateFormat' => 'php:Y-m-d', 'datetimeFormat' => 'php:Y-m-d H:i:s'],
        'errorHandler' => ['errorAction' => 'site/error'],
        'log' => ['targets' => [['class' => \yii\log\FileTarget::class, 'levels' => ['error', 'warning']]]],
        'db' => require __DIR__ . '/db.php',
    ],
];

if (YII_DEBUG && YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
