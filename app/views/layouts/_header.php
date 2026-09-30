<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;

$isLoggedIn = !Yii::$app->user->isGuest;
$user = Yii::$app->user->identity;

$items = [
    [
        'label' => 'داشبورد',
        'url' => ['/site/index'],
        'visible' => $isLoggedIn,
    ],
    [
        'label' => 'بیماران',
        'url' => ['/patient/index'],
        'visible' => $isLoggedIn,
    ],
    [
        'label' => 'پذیرش‌ها',
        'url' => ['/admission/index'],
        'visible' => $isLoggedIn,
    ],
    [
        'label' => 'خدمات',
        'url' => ['/service/index'],
        'visible' => $isLoggedIn,
    ],
    [
        'label' => 'خدمات پذیرش',
        'url' => ['/admission-service/index'],
        'visible' => $isLoggedIn,
    ],
    [
        'label' => 'ترخیص‌ها',
        'url' => ['/discharge/index'],
        'visible' => $isLoggedIn,
    ],
    [
        'label' => 'ورود',
        'url' => ['/site/login'],
        'visible' => !$isLoggedIn,
    ],
    [
        'label' => 'خروج (' . ($user?->username ?? '') . ')',
        'url' => ['/site/logout'],
        'linkOptions' => [
            'data-method' => 'post',
            'class' => 'nav-link logout',
        ],
        'visible' => $isLoggedIn,
    ],
];
?>

<header id="header">
    <?php NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-expand-md navbar-dark bg-dark fixed-top',
        ],
    ]); ?>

    <?= Nav::widget([
        'options' => ['class' => 'navbar-nav me-auto'],
        'encodeLabels' => true,
        'items' => $items,
    ]) ?>

    <?= Html::button('&#127769;', [
        'id' => 'theme-toggle',
        'class' => 'btn btn-link nav-link fs-5',
        'aria-label' => 'تغییر حالت روشن و تاریک',
    ]) ?>

    <?php NavBar::end(); ?>
</header>