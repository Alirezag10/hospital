<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = 'ورود به سامانه';
?>
<div class="auth-shell">
    <?= $this->render('_auth_intro') ?>
    <section class="auth-panel" aria-labelledby="auth-title">
        <h1 id="auth-title">ورود به سامانه</h1><p class="auth-subtitle">با حسابی که مدیر سیستم ساخته است وارد پنل کاری خود شوید.</p>
        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false, 'options' => ['data-loading-form' => '1']]); ?>
        <?= $form->field($model, 'username')->textInput(['dir' => 'ltr', 'autocomplete' => 'username', 'maxlength' => 50, 'autofocus' => true]) ?>
        <?php $toggle = $this->render('/layouts/_password_button', ['inputId' => Html::getInputId($model, 'password')]); ?>
        <?= $form->field($model, 'password', ['template' => "{label}\n<div class=\"password-control\">{input}$toggle</div>\n{error}"])->passwordInput(['dir' => 'ltr', 'autocomplete' => 'current-password', 'maxlength' => 128]) ?>
        <?= $form->field($model, 'rememberMe')->checkbox() ?>
        <?= Html::submitButton('ورود به پنل', ['class' => 'btn btn-primary auth-submit', 'data-loading-label' => 'در حال ورود…']) ?>
        <?php ActiveForm::end(); ?>
        <p class="auth-footnote">برای دریافت حساب یا بازیابی رمز با مدیر سیستم تماس بگیرید.</p>
    </section>
</div>
