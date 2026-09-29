<?php

use app\models\Patient;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */

$patients = ArrayHelper::map(
    Patient::find()->orderBy(['last_name' => SORT_ASC, 'first_name' => SORT_ASC])->all(),
    'id',
    static function (Patient $patient): string {
        return $patient->first_name . ' ' . $patient->last_name
            . ' (' . $patient->national_code . ')';
    }
);
?>

<div class="admission-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'patient_id')->dropDownList(
        $patients,
        ['prompt' => 'بیمار را انتخاب کنید']
    ) ?>

    <?= $form->field($model, 'ward')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doctor_name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>