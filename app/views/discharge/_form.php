<?php

use yii\helpers\Html;
use app\components\ServiceAmount;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Discharge $model */
/** @var app\models\Admission|null $selectedAdmission */
/** @var bool $hasOpenAdmissions */
/** @var app\models\AdmissionService[] $items */

$selectedId = filter_var($model->admission_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$isOpen = $selectedAdmission !== null && $selectedAdmission->status === 'admitted'
    && $selectedAdmission->discharge === null;
$selectedAdmissionLabel = $isOpen ? $selectedAdmission->getSelectionLabel() : '';
$patientName = $isOpen ? $selectedAdmission->patient->first_name . ' ' . $selectedAdmission->patient->last_name : '';
$total = 0;
?>
<div class="discharge-form card" dir="rtl">
    <div class="card-body">
        <?php if (!$isOpen): ?>
        <h2 class="h5 discharge-section-title">انتخاب پذیرش</h2>
        <?php if (!$hasOpenAdmissions): ?>
            <div class="alert alert-info">
                پذیرش بستری و ترخیص‌نشده‌ای برای انتخاب وجود ندارد.
                <?= Html::a('ثبت پذیرش جدید', ['/admission/create'], ['class' => 'alert-link']) ?>
            </div>
        <?php endif; ?>
        <?= Html::beginForm(['create'], 'get', ['id' => 'discharge-preview-form']) ?>
        <?= Html::hiddenInput('admission_id', $isOpen ? $selectedId : '', ['id' => 'discharge-admission-id']) ?>
        <?= Html::label('پذیرش بیمار', 'discharge-admission-search', ['class' => 'form-label required-label']) ?>
        <div class="position-relative entity-search-wrapper">
            <?= Html::textInput('admission_search', $selectedAdmissionLabel, [
                'id' => 'discharge-admission-search',
                'class' => 'form-control',
                'placeholder' => 'نام یا کد ملی بیمار را وارد کنید',
                'autocomplete' => 'off',
                'required' => true,
                'disabled' => !$hasOpenAdmissions,
                'aria-autocomplete' => 'list',
                'role' => 'combobox',
                'aria-haspopup' => 'listbox',
                'aria-controls' => 'discharge-admission-search-results',
                'aria-expanded' => 'false',
                'data-live-search' => 'true',
                'data-target-input' => 'discharge-admission-id',
                'data-search-url' => Url::to(['/admission/search-open-admissions']),
                'data-submit-on-select' => 'true',
            ]) ?>
            <div id="discharge-admission-search-results" class="dropdown-menu w-100 entity-search-dropdown" role="listbox" hidden></div>
        </div>
        <div id="discharge-admission-search-status" class="form-text" aria-live="polite"></div>
        <?= Html::endForm() ?>
        <?php if ($model->hasErrors()): ?>
            <?= Html::errorSummary($model, ['class' => 'alert alert-danger mt-3']) ?>
        <?php endif; ?>
        <p class="discharge-empty-hint">برای نمایش ریز هزینه و ثبت ترخیص، یک پذیرش بستری را جست‌وجو و انتخاب کنید.</p>
        <p class="mt-3 mb-0"><?= Html::a('بازگشت به پذیرش‌ها', ['/admission/index'], ['class' => 'btn btn-outline-secondary']) ?></p>
        <?php else: ?>
            <section class="discharge-patient-summary" aria-label="پذیرش انتخاب‌شده">
                <div class="discharge-patient-heading">
                    <div>
                        <h2><?= Html::encode($patientName) ?> <span class="status-badge status-admitted">بستری</span></h2>
                        <p>کد ملی: <bdi dir="ltr"><?= Html::encode($selectedAdmission->patient->national_code) ?></bdi>
                            <span> · پرونده <?= Yii::$app->formatter->asInteger($selectedId) ?></span></p>
                    </div>
                    <?= Html::a('تغییر پذیرش', ['create'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <dl class="discharge-patient-details">
                    <div><dt>پزشک</dt><dd><?= Html::encode($selectedAdmission->doctor_name) ?></dd></div>
                    <div><dt>بخش</dt><dd><?= Html::encode($selectedAdmission->ward) ?></dd></div>
                    <div><dt>تاریخ پذیرش</dt><dd><?= Html::encode(Yii::$app->formatter->asDatetime($selectedAdmission->admission_date)) ?></dd></div>
                </dl>
            </section>
            <div class="discharge-review-layout">
            <section class="discharge-review-section" aria-labelledby="discharge-review-title">
                <div class="discharge-cost-heading">
                    <h2 id="discharge-review-title">ریز هزینهٔ خدمات</h2>
                    <span>مبالغ به تومان</span>
                </div>
                <div class="table-responsive discharge-cost-table">
                    <table class="table table-striped" id="discharge-cost-preview">
                        <thead><tr><th scope="col">خدمت</th><th scope="col">تعداد</th><th scope="col">قیمت واحد</th><th scope="col">مبلغ</th></tr></thead>
                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="4">
                                خدمتی برای این پذیرش ثبت نشده است.
                                <?= Html::a('افزودن خدمت', ['/admission-service/create', 'admission_id' => $selectedId]) ?>
                            </td></tr>
                        <?php endif; ?>
                        <?php foreach ($items as $item): ?>
                            <?php $amount = ServiceAmount::line($item->quantity, $item->unit_price); $total = ServiceAmount::add($total, $amount); ?>
                            <tr>
                                <td><?= Html::encode($item->service?->title ?? 'خدمت نامشخص') ?></td>
                                <td><?= Yii::$app->formatter->asInteger($item->quantity) ?></td>
                                <td><?= Yii::$app->formatter->asInteger((int) $item->unit_price) ?></td>
                                <td><?= $amount === null ? 'بیش از حد مجاز' : Yii::$app->formatter->asInteger($amount) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="discharge-confirm-section" aria-labelledby="discharge-confirm-title">
                <h2 class="h5 discharge-section-title" id="discharge-confirm-title">ثبت ترخیص</h2>
                <?php $form = ActiveForm::begin(['id' => 'discharge-form', 'action' => ['create', 'admission_id' => $selectedId], 'options' => ['data-loading-form' => '1', 'data-unsaved-warning' => '1']]); ?>
                <?= $form->errorSummary($model) ?>
                <?= Html::activeHiddenInput($model, 'admission_id') ?>
                <?= $form->field($model, 'description')->textarea(['rows' => 2, 'maxlength' => 5000, 'placeholder' => 'در صورت نیاز توضیحی برای پرونده بنویسید'])->label('توضیحات ترخیص (اختیاری)') ?>
                <?php if ($total === null): ?><div class="alert alert-danger">جمع هزینه‌ها از محدودهٔ قابل ثبت بیشتر است. پیش از ترخیص، ریز خدمات را بررسی کنید.</div><?php endif; ?>
                <p class="discharge-note">تاریخ و مبلغ نهایی هنگام ثبت محاسبه می‌شوند. ترخیص ثبت‌شده قابل ویرایش یا حذف نیست.</p>
                <div class="discharge-final-total">
                    <div><span>جمع هزینه خدمات</span><strong id="discharge-preview-total"><?= $total === null ? 'بیش از حد مجاز' : Yii::$app->formatter->asInteger($total) . ' تومان' ?></strong></div>
                    <div class="form-actions">
                    <?= Html::submitButton('ثبت ترخیص', [
                        'class' => 'btn btn-primary',
                        'data-loading-label' => 'در حال ثبت ترخیص…',
                        'data-confirm-title' => 'ثبت قطعی ترخیص',
                        'data-confirm-accept-label' => 'ثبت قطعی ترخیص',
                        'data-confirm' => 'بیمار: ' . $patientName . "\nشماره پرونده: " . Yii::$app->formatter->asInteger($selectedId)
                            . "\nمبلغ محاسبه‌شده: " . ($total === null ? 'بیش از حد مجاز' : Yii::$app->formatter->asInteger($total) . ' تومان')
                            . "\n\nترخیص ثبت‌شده قابل ویرایش یا حذف نیست. مبلغ هنگام ثبت دوباره محاسبه می‌شود.",
                        'disabled' => $total === null,
                    ]) ?>
                    <?= Html::a('بازگشت به پرونده', ['/admission/view', 'id' => $selectedAdmission->id], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </section>
            </div>
        <?php endif; ?>
    </div>
</div>
