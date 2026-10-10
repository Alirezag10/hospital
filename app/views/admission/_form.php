<?php
use app\models\Patient;
use app\models\Doctor;
use app\models\Ward;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var app\models\Admission $model */
/** @var bool $patientLocked */
$selectedPatientId = filter_var($model->patient_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$selectedPatient = $selectedPatientId === false ? null : Patient::findOne(['id' => $selectedPatientId]);
$selectedPatientLabel = $selectedPatient === null
    ? ''
    : $selectedPatient->first_name . ' ' . $selectedPatient->last_name . ' (کد ملی: ' . $selectedPatient->national_code . ')';
$selectedDoctorId = filter_var($model->doctor_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$selectedDoctor = $selectedDoctorId === false ? null : Doctor::findOne(['id' => $selectedDoctorId]);
$selectedDoctorLabel = $selectedDoctor ? $selectedDoctor->name . ' (' . $selectedDoctor->specialty . ')' : '';
$wards = ArrayHelper::map(
    Ward::find()->orderBy(['name' => SORT_ASC])->all(),
    'id',
    static fn(Ward $ward): string => $ward->name . ($ward->floor ? ' (' . $ward->floor . ')' : '')
);
?>
<div class="card"><div class="card-body">
    <?php $form = ActiveForm::begin(['options' => ['data-loading-form' => '1', 'data-unsaved-warning' => '1']]); ?>
    <?= $form->errorSummary($model) ?>
    <?= Html::activeHiddenInput($model, 'patient_id', ['id' => 'admission-patient-id']) ?>
    <div class="mb-3">
        <?= Html::label('بیمار', 'patient-search', ['class' => 'form-label']) ?>
        <?php if ($patientLocked && $selectedPatient !== null): ?>
            <?= Html::textInput('patient_display', $selectedPatientLabel, [
                'id' => 'patient-search',
                'class' => 'form-control',
                'readonly' => true,
            ]) ?>
        <?php else: ?>
            <div class="position-relative entity-search-wrapper">
                <?= Html::textInput('patient_search', $selectedPatientLabel, [
                    'id' => 'patient-search',
                    'class' => 'form-control',
                    'placeholder' => 'نام یا کد ملی بیمار را وارد کنید',
                    'autocomplete' => 'off',
                    'required' => true,
                    'aria-autocomplete' => 'list',
                    'aria-controls' => 'patient-search-results',
                    'aria-expanded' => 'false',
                    'data-live-search' => 'true',
                    'data-target-input' => 'admission-patient-id',
                    'data-search-url' => Url::to(['search-patients']),
                ]) ?>
                <div id="patient-search-results" class="dropdown-menu w-100 entity-search-dropdown" role="listbox" hidden></div>
            </div>
            <div id="patient-search-status" class="form-text" aria-live="polite"></div>
        <?php endif; ?>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <?= Html::activeHiddenInput($model, 'doctor_id', ['id' => 'admission-doctor-id']) ?>
            <?= Html::label('پزشک', 'doctor-search', ['class' => 'form-label']) ?>
            <div class="position-relative entity-search-wrapper">
                <?= Html::textInput('doctor_search', $selectedDoctorLabel, [
                    'id' => 'doctor-search',
                    'class' => 'form-control',
                    'placeholder' => 'نام یا تخصص پزشک را وارد کنید',
                    'autocomplete' => 'off',
                    'required' => true,
                    'aria-autocomplete' => 'list',
                    'aria-controls' => 'doctor-search-results',
                    'aria-expanded' => 'false',
                    'data-live-search' => 'true',
                    'data-target-input' => 'admission-doctor-id',
                    'data-search-url' => Url::to(['search-doctors']),
                ]) ?>
                <div id="doctor-search-results" class="dropdown-menu w-100 entity-search-dropdown" role="listbox" hidden></div>
            </div>
            <div id="doctor-search-status" class="form-text" aria-live="polite"></div>
        </div>
        <div class="col-md-6 mb-3">
            <?= $form->field($model, 'ward_id')->dropDownList($wards, [
                'prompt' => 'بخش را انتخاب کنید',
            ]) ?>
        </div>
    </div>
    <p class="text-muted">تاریخ پذیرش خودکار ثبت می‌شود و وضعیت اولیه بستری است.</p>
    <div class="form-actions">
        <?= Html::submitButton('ثبت پذیرش', ['class' => 'btn btn-primary', 'data-loading-label' => 'در حال ثبت…']) ?>
        <?= Html::a('انصراف', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div></div>
