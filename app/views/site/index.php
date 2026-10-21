<?php
use app\components\Access;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

$user = Yii::$app->user->identity;
$isDoctor = Access::is(User::DOCTOR);
$this->title = 'پنل ' . $user->roleLabel;
$descriptions = [
    User::ADMIN => 'حساب‌های کارکنان و تمام مراحل پرونده بیمار را مدیریت کنید.',
    User::SECRETARY => 'ثبت بیمار و تنظیم پذیرش؛ شروع یک پرونده منظم.',
    User::DOCTOR => 'پرونده بیماران شما، نسخه‌ها و دستورات درمانی در یک جا.',
    User::DISCHARGE => 'بررسی ریز هزینه‌های درمان و تکمیل ترخیص بیمار.',
];
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
        <div>
            <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
            <p><?= Html::encode($user->full_name) ?>، <?= Html::encode($descriptions[$user->role]) ?></p>
        </div>
        <?= $this->render('/layouts/_icon', ['name' => $isDoctor ? 'doctor' : 'admission']) ?>
    </div>
    <div data-live-panel data-live-url="<?= Html::encode(Url::to(['/site/dashboard'])) ?>">
        <?= $this->render('/layouts/_live_status') ?>
        <div data-live-content>
            <?= $this->render('_dashboard', $dashboard) ?>
        </div>
    </div>

    <h2 class="quick-title">عملیات مرتبط</h2>
    <div class="dashboard-actions">
        <?php foreach ($actions as [$label, $description, $route, $icon]): ?>
            <?php if (!Access::allows($route) || ($icon === 'doctor' && !$isDoctor)) { continue; } ?>
            <?= Html::a(
                $this->render('/layouts/_icon', ['name' => $icon]) . Html::encode($label),
                [$route],
                ['class' => 'btn btn-outline-secondary', 'title' => $description]
            ) ?>
        <?php endforeach; ?>
    </div>
</div>
