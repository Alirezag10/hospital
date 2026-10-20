<?php
use app\components\Access;
use app\models\User;
use yii\helpers\Html;
$user = Yii::$app->user->identity;
$this->title = 'پنل ' . $user->roleLabel;
$descriptions = [User::ADMIN => 'حساب‌های کارکنان و تمام مراحل پرونده بیمار را مدیریت کنید.',
    User::SECRETARY => 'ثبت بیمار و تنظیم پذیرش؛ شروع یک پرونده منظم.',
    User::DOCTOR => 'پرونده بیماران شما، نسخه‌ها و دستورات درمانی در یک جا.',
    User::DISCHARGE => 'بررسی ریز هزینه‌های درمان و تکمیل ترخیص بیمار.'];
$actions = [
    ['مدیریت کاربران', 'حساب بسازید، نقش تعیین کنید و دسترسی کارکنان را مدیریت کنید.', '/user/index', 'patient'],
    ['ثبت بیمار', 'مشخصات بیمار جدید را وارد کنید.', '/patient/create', 'patient'],
    ['ثبت پذیرش', 'پزشک و بخش بیمار را تعیین کنید.', '/admission/create', 'admission'],
    ['بیماران من', 'پرونده را باز کنید، نسخه بنویسید و چاپ بگیرید.', '/admission/index', 'doctor'],
    ['ثبت خدمت', 'خدمت انجام‌شده برای بیمار خود را ثبت کنید.', '/admission-service/create', 'service'],
    ['ثبت ترخیص', 'هزینه خدمات، دارو و مصرفی‌ها را بررسی کنید.', '/discharge/create', 'discharge'],
];
?>
<div class="site-index">
    <div class="dashboard-intro">
        <div><h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
            <p><?= Html::encode($user->full_name) ?>، <?= Html::encode($descriptions[$user->role]) ?></p></div>
        <?= $this->render('/layouts/_icon', ['name' => Access::is(User::DOCTOR) ? 'doctor' : 'admission']) ?>
    </div>
    <section class="dashboard-stats" aria-label="آمار پنل">
        <?php foreach ($stats as [$label, $count, $icon, $route]): ?>
            <?= Html::beginTag('a', ['href' => \yii\helpers\Url::to($route), 'class' => 'dashboard-stat']) ?>
                <span class="stat-label"><?= $this->render('/layouts/_icon', ['name' => $icon]) ?><?= Html::encode($label) ?></span>
                <strong><?= Yii::$app->formatter->asInteger($count) ?></strong>
            <?= Html::endTag('a') ?>
        <?php endforeach; ?>
    </section>
    <h2 class="quick-title">کارهای شما</h2>
    <div class="row g-3">
        <?php foreach ($actions as [$label, $description, $route, $icon]): ?>
            <?php if (!Access::allows($route) || ($icon === 'doctor' && !Access::is(User::DOCTOR))) { continue; } ?>
            <div class="col-lg-6"><?= Html::a(
                Html::tag('strong', $this->render('/layouts/_icon', ['name' => $icon]) . Html::encode($label)) . Html::tag('small', Html::encode($description)),
                [$route], ['class' => 'card action-card']) ?></div>
        <?php endforeach; ?>
    </div>
</div>
