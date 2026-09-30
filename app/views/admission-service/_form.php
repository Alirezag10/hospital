<?php

use app\models\Admission;
use app\models\Service;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AdmissionService $model */

$admissions = ArrayHelper::map(
    Admission::find()
        ->with('patient')
        ->where(['status' => 'admitted'])
        ->orderBy(['id' => SORT_DESC])
        ->all(),
    'id',
    static function (Admission $admission): string {
        $patient = $admission->patient;

        $patientName = $patient
            ? $patient->first_name . ' ' . $patient->last_name
            : 'بیمار نامشخص';

        return '#' . $admission->id . ' — ' . $patientName;
    }
);

$services = ArrayHelper::map(
    Service::find()
        ->where(['active' => 1])
        ->orderBy(['title' => SORT_ASC])
        ->all(),
    'id',
    static function (Service $service): string {
        return $service->title
            . ' (' . number_format($service->price) . ' تومان)';
    }
);

// در ویرایش، گزینهٔ قبلی حتی اگر دیگر فعال نباشد نمایش داده می‌شود.
// اعتبارسنجی هنگام ذخیره، فعال بودن آن را بررسی می‌کند.
if (
    !$model->isNewRecord
    && !array_key_exists($model->admission_id, $admissions)
) {
    $admission = $model->admission;

    if ($admission !== null) {
        $patient = $admission->patient;

        $patientName = $patient
            ? $patient->first_name . ' ' . $patient->last_name
            : 'بیمار نامشخص';

        $admissions[$admission->id] =
            '#' . $admission->id . ' — ' . $patientName
            . ' (غیربستری)';
    }
}

if (
    !$model->isNewRecord
    && !array_key_exists($model->service_id, $services)
) {
    $service = $model->service;

    if ($service !== null) {
        $services[$service->id] =
            $service->title . ' (غیرفعال)';
    }
}
?>

<div class="admission-service-form card" dir="rtl">
    <div class="card-body">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'admission_id')
        ->dropDownList($admissions, [
            'prompt' => 'پذیرش بیمار را انتخاب کنید',
        ]) ?>

    <?= $form->field($model, 'service_id')
        ->dropDownList($services, [
            'prompt' => 'خدمت را انتخاب کنید',
        ]) ?>

    <?= $form->field($model, 'quantity')
        ->input('number', [
            'min' => 1,
            'step' => 1,
        ]) ?>

    <?php if (!$model->isNewRecord): ?>
        <p>
            قیمت واحد ثبت‌شده:
            <strong>
                <?= Html::encode(
                    number_format($model->getOldAttribute('unit_price'))
                ) ?>
                تومان
            </strong>
        </p>
    <?php endif; ?>

    <p class="text-muted">
        قیمت هنگام ثبت از تعرفهٔ خدمت گرفته می‌شود.
        تغییر تعداد، قیمت واحد قبلی را تغییر نمی‌دهد؛
        انتخاب خدمت جدید با تعرفهٔ فعلی آن ثبت می‌شود.
    </p>

    <div class="d-flex flex-wrap gap-2">
        <?= Html::submitButton($model->isNewRecord ? 'ثبت خدمت' : 'ذخیره تغییرات', [
            'class' => 'btn btn-primary',
        ]) ?>
    </div>

    <p class="mt-3 mb-0"><?= Html::a('انصراف', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?></p>

    <?php ActiveForm::end(); ?>

    </div>
</div>