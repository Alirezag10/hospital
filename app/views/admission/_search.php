<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\AdmissionSearch $model */
?>
<div class="card mb-3"><div class="card-body">
    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index'], 'enableClientValidation' => false]); ?>
    <div class="row">
        <div class="col-md-3"><?= $form->field($model, 'name')->textInput(['maxlength' => 200, 'placeholder' => 'نام یا نام خانوادگی']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'national_code')->textInput(['dir' => 'ltr', 'inputmode' => 'numeric', 'maxlength' => 20, 'placeholder' => 'تمام یا بخشی از کد ملی']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'mobile')->textInput(['dir' => 'ltr', 'inputmode' => 'tel', 'placeholder' => 'شماره موبایل بیمار']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'status')->dropDownList([
            'admitted' => 'بستری', 'discharged' => 'ترخیص‌شده',
        ], ['prompt' => 'همهٔ وضعیت‌ها', 'class' => 'form-select']) ?></div>
    </div>
    <?= $form->errorSummary($model) ?>
    <div class="form-actions">
        <?= Html::submitButton('جست‌وجو', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('پاک کردن فیلترها', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div></div>
