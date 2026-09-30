<?php

use app\models\Admission;
use app\models\Discharge;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Discharge $model */

$admissions = ArrayHelper::map(
    Admission::find()
        ->with('patient')
        ->where(['status' => 'admitted'])
        ->andWhere([
            'not in',
            'id',
            Discharge::find()->select('admission_id'),
        ])
        ->orderBy(['id' => SORT_DESC])
        ->all(),
    'id',
    static function (Admission $admission): string {
        $patient = $admission->patient;

        $name = $patient
            ? $patient->first_name . ' ' . $patient->last_name
            : 'بیمار نامشخص';

        return '#' . $admission->id . ' — ' . $name;
    }
);
?>

<div class="discharge-form card" dir="rtl">
    <div class="card-body">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model) ?>

    <?php if (empty($admissions)): ?>
        <div class="alert alert-info">
            پذیرش بستری و ترخیص‌نشده‌ای برای انتخاب وجود ندارد.
        </div>
    <?php endif; ?>

    <?= $form->field($model, 'admission_id')
        ->dropDownList($admissions, [
            'prompt' => 'پذیرش بیمار را انتخاب کنید',
        ]) ?>

    <?= $form->field($model, 'description')
        ->textarea([
            'rows' => 4,
            'placeholder' => 'توضیحات ترخیص، در صورت نیاز',
        ]) ?>

    <div class="alert alert-info">
        مبلغ نهایی از مجموع تعداد × قیمت واحد ثبت‌شدهٔ خدمات
        محاسبه می‌شود. تاریخ ترخیص و تاریخ ثبت خودکار تعیین می‌شوند.
    </div>

    <p class="text-muted">
        پس از ثبت، ترخیص قابل ویرایش یا حذف نیست.
    </p>

    <div class="d-flex flex-wrap gap-2">
        <?= Html::submitButton('ثبت ترخیص', [
            'class' => 'btn btn-primary',
            'disabled' => empty($admissions),
            'data-confirm' => 'ترخیص این پذیرش ثبت شود؟',
        ]) ?>
    </div>

    <p class="mt-3 mb-0"><?= Html::a('انصراف', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?></p>

    <?php ActiveForm::end(); ?>

    </div>
</div>