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
        return '#' . $admission->id . ' - '
            . $admission->patient->first_name . ' '
            . $admission->patient->last_name;
    }
);

$services = ArrayHelper::map(
    Service::find()->where(['active' => 1])->orderBy(['title' => SORT_ASC])->all(),
    'id',
    static function (Service $service): string {
        return $service->title . ' (' . $service->price . ' تومان)';
    }
);
?>

<div class="admission-service-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'admission_id')
        ->dropDownList($admissions, ['prompt' => 'پذیرش را انتخاب کنید']) ?>

    <?= $form->field($model, 'service_id')
        ->dropDownList($services, ['prompt' => 'خدمت را انتخاب کنید']) ?>

    <?= $form->field($model, 'quantity')
        ->input('number', ['min' => 1, 'step' => 1]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>