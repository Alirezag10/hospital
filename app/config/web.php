<?php
$cookieKey = getenv('HOSPITAL_COOKIE_VALIDATION_KEY');
if ($cookieKey === false || $cookieKey === '') {
    $cookieKeyFile = __DIR__ . '/cookie-key.php';
    $cookieKey = is_file($cookieKeyFile) ? require $cookieKeyFile : '';
}
if (!is_string($cookieKey) || strlen($cookieKey) < 32) {
    throw new \RuntimeException('Set HOSPITAL_COOKIE_VALIDATION_KEY or create app/config/cookie-key.php with a random key of at least 32 characters.');
}

$config = [
    'id' => 'hospital',
    'name' => 'سامانه بیمارستان',
    'basePath' => dirname(__DIR__),
    'language' => 'fa-IR',
    'timeZone' => 'Asia/Tehran',
    'bootstrap' => ['log'],
    'container' => [
        'definitions' => [
            \yii\widgets\ActiveForm::class => [
                'validateOnBlur' => true,
                'validateOnChange' => true,
                'validateOnType' => false,
                'validateOnSubmit' => true,
            ],
        ],
    ],
    'aliases' => ['@bower' => '@vendor/bower-asset', '@npm' => '@vendor/npm-asset'],
    'components' => [
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
            'identityCookie' => ['name' => '_hospital_identity', 'httpOnly' => true, 'sameSite' => 'Lax'],
        ],
        'session' => ['name' => 'HOSPITAL_SESSION', 'cookieParams' => ['httpOnly' => true, 'sameSite' => 'Lax']],
        'assetManager' => ['appendTimestamp' => true],
        'request' => ['cookieValidationKey' => $cookieKey],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
        'formatter' => [
            'class' => \app\components\HospitalFormatter::class,
            'locale' => 'fa_IR@calendar=persian',
            'calendar' => \IntlDateFormatter::TRADITIONAL,
            'dateFormat' => 'yyyy/MM/dd',
            'datetimeFormat' => 'yyyy/MM/dd HH:mm',
            'defaultTimeZone' => 'Asia/Tehran',
            'timeZone' => 'Asia/Tehran',
        ],
        'errorHandler' => ['errorAction' => 'site/error'],
        'log' => ['targets' => [['class' => \yii\log\FileTarget::class, 'levels' => ['error', 'warning']]]],
        'db' => require __DIR__ . '/db.php',
    ],
];

if (YII_DEBUG && YII_ENV_DEV && class_exists(\yii\debug\Module::class)) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
