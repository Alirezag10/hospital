<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\Doctor $model */
$this->title = 'ثبت پزشک جدید';
$this->params['breadcrumbs'][] = ['label' => 'پزشکان', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="doctor-create form-page" dir="rtl">
    <div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>اطلاعات پزشک برای جست‌وجو در زمان پذیرش استفاده می‌شود.</p></div></div>
    <div class="card"><div class="card-body">
        <?php $form = ActiveForm::begin(['options' => ['data-loading-form' => '1', 'data-unsaved-warning' => '1']]); ?>
        <?= $form->errorSummary($model) ?>
        <fieldset class="form-section"><legend>مشخصات پزشک</legend><div class="row g-3">
            <div class="col-md-6"><?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'مثلاً دکتر علی رضایی']) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'specialty')->textInput(['maxlength' => true, 'placeholder' => 'مثلاً داخلی']) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'mobile', ['enableClientValidation' => false])->textInput(['maxlength' => true, 'dir' => 'ltr', 'inputmode' => 'tel', 'placeholder' => '09123456789']) ?></div>
        </div></fieldset>
        <div class="form-actions">
            <?= Html::submitButton('ثبت پزشک', ['class' => 'btn btn-primary', 'data-loading-label' => 'در حال ثبت…']) ?>
            <?= Html::a('انصراف', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div></div>
</div>
