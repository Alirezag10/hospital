<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PatientSearch $model */
?>
<div class="card mb-4" dir="rtl">
    <div class="card-body">
        <h2 class="h5 mb-3">جست‌وجوی بیمار</h2>
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'enableClientValidation' => false,
        ]); ?>
        <?= $form->errorSummary($model) ?>
        <div class="row">
            <div class="col-md-6 col-lg-3">
                <?= $form->field($model, 'first_name')->textInput(['placeholder' => 'نام یا بخشی از آن']) ?>
            </div>
            <div class="col-md-6 col-lg-3">
                <?= $form->field($model, 'last_name')->textInput(['placeholder' => 'نام خانوادگی یا بخشی از آن']) ?>
            </div>
            <div class="col-md-6 col-lg-3">
                <?= $form->field($model, 'national_code')->textInput([
                    'dir' => 'ltr',
                    'inputmode' => 'numeric',
                    'placeholder' => 'کد ملی با ارقام انگلیسی',
                ]) ?>
            </div>
            <div class="col-md-6 col-lg-3">
                <?= $form->field($model, 'mobile')->textInput([
                    'dir' => 'ltr',
                    'inputmode' => 'tel',
                    'placeholder' => 'شماره موبایل',
                ]) ?>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::submitButton('جست‌وجو', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('پاک کردن فیلترها', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
