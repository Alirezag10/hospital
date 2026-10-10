<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\Ward $model */
$this->title = 'ثبت بخش جدید';
$this->params['breadcrumbs'][] = ['label' => 'بخش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ward-create form-page" dir="rtl">
    <div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>اطلاعات بخش برای انتخاب هنگام پذیرش ثبت می‌شود.</p></div></div>
    <div class="card"><div class="card-body">
        <?php $form = ActiveForm::begin(['options' => ['data-loading-form' => '1', 'data-unsaved-warning' => '1']]); ?>
        <?= $form->errorSummary($model) ?>
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'مثلاً داخلی']) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'floor')->textInput(['maxlength' => true, 'placeholder' => 'مثلاً طبقه دوم']) ?></div>
        </div>
        <div class="form-actions">
            <?= Html::submitButton('ثبت بخش', ['class' => 'btn btn-primary', 'data-loading-label' => 'در حال ثبت…']) ?>
            <?= Html::a('انصراف', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div></div>
</div>
