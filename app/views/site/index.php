<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int|string $patients */
/** @var int|string $activeAdmissions */
/** @var int|string $discharges */

$this->title = 'داشبورد بیمارستان';
?>
<div class="site-index" dir="rtl">
    <h1 class="h3 mb-2"><?= Html::encode($this->title) ?></h1>
    <p class="text-muted mb-4">مدیریت بیماران، پذیرش، خدمات و ترخیص</p>

    <?php if (Yii::$app->user->isGuest): ?>
        <div class="card">
            <div class="card-body">
                <h2 class="h5">ورود به سامانه</h2>
                <p class="text-muted">برای مشاهدهٔ آمار و مدیریت پرونده‌ها وارد حساب خود شوید.</p>
                <?= Html::a('ورود', ['/site/login'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 text-muted">بیماران ثبت‌شده</h2>
                        <p class="display-6 fw-semibold my-3"><?= Html::encode(number_format((int) $patients)) ?></p>
                        <?= Html::a('مشاهده بیماران', ['/patient/index'], ['class' => 'btn btn-outline-primary mt-auto']) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 text-muted">پذیرش‌های فعال</h2>
                        <p class="display-6 fw-semibold my-3"><?= Html::encode(number_format((int) $activeAdmissions)) ?></p>
                        <?= Html::a('مشاهده پذیرش‌های بستری', ['/admission/index', 'AdmissionSearch' => ['status' => 'admitted']], ['class' => 'btn btn-outline-success mt-auto']) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 text-muted">ترخیص‌های ثبت‌شده</h2>
                        <p class="display-6 fw-semibold my-3"><?= Html::encode(number_format((int) $discharges)) ?></p>
                        <?= Html::a('مشاهده ترخیص‌ها', ['/discharge/index'], ['class' => 'btn btn-outline-secondary mt-auto']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-2">دسترسی سریع</h2>
                <p class="text-muted">بیمار را ثبت کنید، پذیرش بسازید، خدمات را اضافه کنید و در پایان ترخیص را ثبت کنید.</p>
                <div class="d-flex flex-wrap gap-2">
                    <?= Html::a('ثبت بیمار', ['/patient/create'], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ثبت پذیرش', ['/admission/create'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('ثبت خدمت برای پذیرش', ['/admission-service/create'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('ثبت ترخیص', ['/discharge/create'], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
