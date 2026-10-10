<?php
use app\models\Admission;
use app\models\Service;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var app\models\AdmissionService $model */
/** @var bool $admissionLocked */

$selectedAdmissionId = filter_var($model->admission_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$selectedAdmission = $selectedAdmissionId === false
    ? null
    : Admission::find()->with('patient')->where(['id' => $selectedAdmissionId])->one();
$selectedAdmissionLabel = $selectedAdmission ? $selectedAdmission->getSelectionLabel() : '';
$serviceModels = Service::find()->where(['active' => 1])->orderBy(['title' => SORT_ASC])->all();
$services = ArrayHelper::map($serviceModels, 'id', static function ($service) {
    return $service->title . ' (' . number_format($service->price) . ' تومان)';
});
$serviceOptions = [];
foreach ($serviceModels as $service) {
    $serviceOptions[$service->id] = ['data-price' => (int) $service->price];
}
?>
<div class="card"><div class="card-body">
    <?php $form = ActiveForm::begin(['options' => ['data-loading-form' => '1', 'data-unsaved-warning' => '1']]); ?>
    <?= $form->errorSummary($model) ?>
    <?= Html::activeHiddenInput($model, 'admission_id', ['id' => 'admission-service-admission-id']) ?>
    <div class="mb-3">
        <?= Html::label('پذیرش بیمار', 'admission-search', ['class' => 'form-label']) ?>
        <?php if ($admissionLocked && $selectedAdmission !== null): ?>
            <?= Html::textInput('admission_display', $selectedAdmissionLabel, [
                'id' => 'admission-search',
                'class' => 'form-control',
                'readonly' => true,
            ]) ?>
            <section class="selected-admission-summary" aria-label="خلاصه پذیرش انتخاب‌شده">
                <strong><?= Html::encode($selectedAdmission->patient->first_name . ' ' . $selectedAdmission->patient->last_name) ?></strong>
                <span>کد ملی: <bdi><?= Html::encode($selectedAdmission->patient->national_code) ?></bdi></span>
                <span>پزشک: <?= Html::encode($selectedAdmission->doctor_name) ?></span>
                <span>بخش: <?= Html::encode($selectedAdmission->ward) ?></span>
            </section>
        <?php else: ?>
            <div class="position-relative entity-search-wrapper">
                <?= Html::textInput('admission_search', $selectedAdmissionLabel, [
                    'id' => 'admission-search',
                    'class' => 'form-control',
                    'placeholder' => 'نام یا کد ملی بیمار را وارد کنید',
                    'autocomplete' => 'off',
                    'required' => true,
                    'aria-autocomplete' => 'list',
                    'aria-controls' => 'admission-search-results',
                    'aria-expanded' => 'false',
                    'data-live-search' => 'true',
                    'data-target-input' => 'admission-service-admission-id',
                    'data-search-url' => Url::to(['/admission/search-open-admissions']),
                ]) ?>
                <div id="admission-search-results" class="dropdown-menu w-100 entity-search-dropdown" role="listbox" hidden></div>
            </div>
            <section class="selected-admission-summary" data-selected-admission-summary hidden aria-label="خلاصه پذیرش انتخاب‌شده">
                <strong data-summary-patient></strong>
                <span>کد ملی: <bdi data-summary-code></bdi></span>
                <span>پزشک: <span data-summary-doctor></span></span>
                <span>بخش: <span data-summary-ward></span></span>
            </section>
            <div id="admission-search-status" class="form-text" aria-live="polite"></div>
        <?php endif; ?>
    </div>
    <?= $form->field($model, 'service_id')->dropDownList($services, ['prompt' => 'انتخاب خدمت', 'options' => $serviceOptions, 'id' => 'admission-service-select', 'data-service-price-select' => '1']) ?>
    <?= $form->field($model, 'quantity')->input('number', ['min' => 1, 'step' => 1, 'id' => 'admission-service-quantity', 'data-service-quantity' => '1']) ?>
    <p class="service-cost-preview" aria-live="polite">برآورد هزینه این ردیف: <strong data-service-total>۰ تومان</strong><small>مبلغ نهایی در سرور محاسبه می‌شود.</small></p>
    <div class="form-actions">
        <?= Html::submitButton('ثبت خدمت', ['class' => 'btn btn-primary', 'data-loading-label' => 'در حال ثبت…']) ?>
        <?= Html::a('انصراف', $model->admission_id ? ['/admission/view', 'id' => $model->admission_id] : ['/admission/index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div></div>
