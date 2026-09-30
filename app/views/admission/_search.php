<?php

use app\models\Doctor;
use app\models\Ward;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AdmissionSearch $model */

$doctors = ArrayHelper::map(
    Doctor::find()->orderBy(['name' => SORT_ASC])->all(),
    'id',
    'name'
);

$wards = ArrayHelper::map(
    Ward::find()->orderBy(['name' => SORT_ASC])->all(),
    'id',
    'name'
);
?>

<div class="card mb-4" dir="rtl">
    <div class="card-body">
        <h2 class="h5 mb-3">جست‌وجوی پذیرش‌ها</h2>

        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'enableClientValidation' => false,
        ]); ?>

        <?= $form->errorSummary($model) ?>

        <div class="row">
            <div class="col-md-6 col-lg-4">
                <?= $form->field($model, 'patientName')
                    ->label('نام بیمار')
                    ->textInput([
                        'placeholder' => 'نام یا نام خانوادگی بیمار',
                    ]) ?>
            </div>

            <div class="col-md-6 col-lg-4">
                <?= $form->field($model, 'doctor_id')
                    ->label('پزشک')
                    ->dropDownList($doctors, [
                        'prompt' => 'همهٔ پزشکان',
                    ]) ?>
            </div>

            <div class="col-md-6 col-lg-4">
                <?= $form->field($model, 'ward_id')
                    ->label('بخش')
                    ->dropDownList($wards, [
                        'prompt' => 'همهٔ بخش‌ها',
                    ]) ?>
            </div>

            <div class="col-md-6 col-lg-4">
                <?= $form->field($model, 'status')
                    ->label('وضعیت')
                    ->dropDownList([
                        'admitted' => 'بستری',
                        'discharged' => 'ترخیص‌شده',
                    ], [
                        'prompt' => 'همهٔ وضعیت‌ها',
                    ]) ?>
            </div>

            <div class="col-md-6 col-lg-4">
                <?= $form->field($model, 'dateFrom')
                    ->label('پذیرش از تاریخ')
                    ->input('date') ?>
            </div>

            <div class="col-md-6 col-lg-4">
                <?= $form->field($model, 'dateTo')
                    ->label('پذیرش تا تاریخ')
                    ->input('date') ?>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-2">
            <?= Html::submitButton('جست‌وجو', [
                'class' => 'btn btn-primary',
            ]) ?>

            <?= Html::a('پاک کردن فیلترها', ['index'], [
                'class' => 'btn btn-outline-secondary',
            ]) ?>
        </div>

        <p class="text-muted small mt-3 mb-0">
            برای نمایش همهٔ پذیرش‌ها، فیلترها را خالی بگذارید.
            تاریخ‌ها شمسی هستند؛ از تقویم انتخاب کنید یا با قالب ۱۴۰۵/۰۷/۰۸ وارد کنید.
        </p>

        <?php ActiveForm::end(); ?>
    </div>
</div>