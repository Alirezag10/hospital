<?php
use app\models\Patient;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$patients = ArrayHelper::map(Patient::find()->orderBy(['last_name' => SORT_ASC])->all(), 'id', static function ($patient) {
    return $patient->first_name . ' ' . $patient->last_name . ' — کد ملی: ' . $patient->national_code;
});
?>
<div class="card"><div class="card-body">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->errorSummary($model) ?>
    <?= $form->field($model, 'patient_id')->dropDownList($patients, ['prompt' => 'انتخاب بیمار']) ?>
    <div class="row">
        <div class="col-md-6"><?= $form->field($model, 'doctor_name')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-6"><?= $form->field($model, 'ward')->textInput(['maxlength' => true, 'placeholder' => 'مثلاً داخلی یا جراحی']) ?></div>
    </div>
    <p class="text-muted">تاریخ پذیرش خودکار ثبت می‌شود و وضعیت اولیه بستری است.</p>
    <?= Html::submitButton('ثبت پذیرش', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('انصراف', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?php ActiveForm::end(); ?>
</div></div>
