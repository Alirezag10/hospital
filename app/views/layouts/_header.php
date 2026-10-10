<?php
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;

NavBar::begin([
    'brandLabel' => '<span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 40 40" focusable="false"><path class="hospital-building" d="M10 35V9h20v26M4 35V18h6m20 0h6v17M3 35h34M17 35v-8h6v8M14 21h3m6 0h3M7 24h3m20 0h3M7 29h3m20 0h3"/><path class="hospital-cross" d="M20 12v6m-3-3h6"/></svg></span>' . Html::tag('span', Html::encode(Yii::$app->name)),
    'brandUrl' => Yii::$app->homeUrl,
    'screenReaderToggleText' => 'نمایش یا بستن منو',
    'options' => ['class' => 'navbar-expand-lg navbar-light bg-white sticky-top'],
]);
$items = [];
foreach ([
    ['داشبورد', 'dashboard', '/site/index'],
    ['بیماران', 'patient', '/patient/index'],
    ['پزشکان', 'doctor', '/doctor/index'],
    ['بخش‌ها', 'ward', '/ward/index'],
    ['پذیرش‌ها', 'admission', '/admission/index'],
    ['ترخیص', 'discharge', '/discharge/create'],
] as [$label, $icon, $route]) {
    $items[] = ['label' => $this->render('_icon', ['name' => $icon]) . Html::tag('span', Html::encode($label)), 'url' => [$route]];
}

echo Nav::widget(['encodeLabels' => false, 'options' => ['class' => 'navbar-nav'], 'items' => $items]);
NavBar::end();
