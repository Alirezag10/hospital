<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Patient $model */
?>
<div class="patient-form card" dir="rtl">
    <div class="card-body">
        <p class="text-muted mb-3">مشخصات بیمار را وارد کنید. تاریخ تولد اختیاری است.</p>
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->errorSummary($model) ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'first_name')->textInput([
                    'maxlength' => true,
                    'autocomplete' => 'given-name',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'last_name')->textInput([
                    'maxlength' => true,
                    'autocomplete' => 'family-name',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'national_code', ['enableClientValidation' => false])->textInput([
                    'maxlength' => true,
                    'dir' => 'ltr',
                    'inputmode' => 'numeric',
                    'placeholder' => 'مثلاً 0012345678',
                ])->hint('۱۰ رقم؛ ارقام فارسی و انگلیسی پذیرفته می‌شوند و صفر ابتدای کد حفظ می‌شود.') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'mobile', ['enableClientValidation' => false])->textInput([
                    'maxlength' => true,
                    'dir' => 'ltr',
                    'inputmode' => 'tel',
                    'autocomplete' => 'tel',
                    'placeholder' => 'مثلاً 09123456789',
                ])->hint('۱۱ رقم با شروع ۰۹؛ ارقام فارسی و انگلیسی پذیرفته می‌شوند.') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'birth_date')->input('date', [
                    'dir' => 'ltr',
                    'max' => date('Y-m-d'),
                ])->hint('تاریخ میلادی؛ در صورت نامشخص بودن خالی بگذارید.') ?>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2">
            <?= Html::submitButton('ثبت بیمار', [
                'class' => 'btn btn-primary',
            ]) ?>
            <?= Html::a('انصراف', ['index'], [
                'class' => 'btn btn-outline-secondary',
            ]) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
