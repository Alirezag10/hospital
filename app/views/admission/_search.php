<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\AdmissionSearch $model */
?>
<div class="card mb-3 search-panel"><div class="card-body">
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
    <div class="form-actions search-actions">
        <?= Html::submitButton('جست‌وجو', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('پاک کردن فیلترها', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
    <?= $this->render('/layouts/_filters', ['formName' => $model->formName(), 'filters' => [
        'name' => ['نام', $model->name], 'national_code' => ['کد ملی', $model->national_code],
        'mobile' => ['موبایل', $model->mobile],
        'status' => ['وضعیت', is_string($model->status) ? (['admitted' => 'بستری', 'discharged' => 'ترخیص‌شده'][$model->status] ?? $model->status) : ''],
    ]]) ?>
</div></div>
