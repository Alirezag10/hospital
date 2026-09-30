<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

$this->title = 'ورود به سامانه';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'ورود به سامانه مدیریت بیمارستان';
?>
<div class="site-login d-flex align-items-center justify-content-center py-4" dir="rtl">
    <div class="card border-0 overflow-hidden login-split-card">
        <div class="row g-0">
            <div class="col-md-5 d-none d-md-flex login-brand-panel text-white">
                <div class="d-flex flex-column justify-content-center p-4 p-lg-5 w-100">
                    <h2 class="h3 fw-bold mb-3"><?= Html::encode(Yii::$app->name) ?></h2>
                    <p class="opacity-75 mb-0">ثبت بیمار، مدیریت پذیرش، ارائه خدمات و ترخیص در یک سامانه.</p>
                </div>
            </div>
            <div class="col-md-7">
                <div class="p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2"><?= Html::encode($this->title) ?></h1>
                    <p class="text-muted mb-4">نام کاربری و رمز عبور حساب خود را وارد کنید.</p>
                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                    <?= $form->errorSummary($model) ?>
                    <?= $form->field($model, 'username')->textInput([
                        'autofocus' => true,
                        'autocomplete' => 'username',
                        'dir' => 'ltr',
                    ])->label('نام کاربری') ?>
                    <?= $form->field($model, 'password')->passwordInput([
                        'autocomplete' => 'current-password',
                        'dir' => 'ltr',
                    ])->label('رمز عبور') ?>
                    <?= $form->field($model, 'rememberMe')->checkbox()->label('مرا به خاطر بسپار') ?>
                    <div class="d-grid mt-3">
                        <?= Html::submitButton('ورود', [
                            'class' => 'btn btn-primary btn-lg',
                            'name' => 'login-button',
                        ]) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
