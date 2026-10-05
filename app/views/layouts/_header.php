<?php
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;

NavBar::begin([
    'brandLabel' => '<span class="brand-mark" aria-hidden="true">+</span>' . Html::encode(Yii::$app->name),
    'brandUrl' => Yii::$app->homeUrl,
    'screenReaderToggleText' => 'نمایش یا بستن منو',
    'options' => ['class' => 'navbar-expand-md navbar-light bg-white sticky-top'],
]);
echo Nav::widget(['options' => ['class' => 'navbar-nav'], 'items' => [
    ['label' => 'داشبورد', 'url' => ['/site/index']],
    ['label' => 'بیماران', 'url' => ['/patient/index']],
    ['label' => 'پذیرش‌ها', 'url' => ['/admission/index']],
]]);
NavBar::end();
