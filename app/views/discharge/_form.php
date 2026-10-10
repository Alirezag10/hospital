<?php

use yii\helpers\Html;
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
$total = 0;
?>
<div class="discharge-form card" dir="rtl">
    <div class="card-body">
        <h2 class="h5 discharge-section-title">انتخاب پذیرش و بررسی هزینه</h2>
        <?php if (!$hasOpenAdmissions): ?>
            <div class="alert alert-info">
                پذیرش بستری و ترخیص‌نشده‌ای برای انتخاب وجود ندارد.
                <?= Html::a('ثبت پذیرش جدید', ['/admission/create'], ['class' => 'alert-link']) ?>
            </div>
        <?php endif; ?>
        <?= Html::beginForm(['create'], 'get', ['id' => 'discharge-preview-form']) ?>
        <?= Html::hiddenInput('admission_id', $isOpen ? $selectedId : '', ['id' => 'discharge-admission-id']) ?>
        <?= Html::label('پذیرش بیمار', 'discharge-admission-search', ['class' => 'form-label']) ?>
        <div class="position-relative entity-search-wrapper">
            <?= Html::textInput('admission_search', $selectedAdmissionLabel, [
                'id' => 'discharge-admission-search',
                'class' => 'form-control',
                'placeholder' => 'نام یا کد ملی بیمار را وارد کنید',
                'autocomplete' => 'off',
                'required' => true,
                'disabled' => !$hasOpenAdmissions,
                'aria-autocomplete' => 'list',
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

        <?php if ($isOpen): ?>
            <div class="mt-4">
                <h2 class="h5">ریز خدمات پذیرش</h2>
                <section class="selected-admission-summary" aria-label="خلاصه پذیرش انتخاب‌شده">
                    <strong><?= Html::encode($selectedAdmission->patient->first_name . ' ' . $selectedAdmission->patient->last_name) ?></strong>
                    <span><small>کد ملی</small><bdi><?= Html::encode($selectedAdmission->patient->national_code) ?></bdi></span>
                    <span><small>پزشک</small><?= Html::encode($selectedAdmission->doctor_name) ?></span>
                    <span><small>بخش</small><?= Html::encode($selectedAdmission->ward) ?></span>
                </section>
                <div class="discharge-cost-heading">
                    <h3>خدمات ثبت‌شده</h3>
                    <span>مبالغ به تومان</span>
                </div>
                <div class="table-responsive discharge-cost-table">
                    <table class="table table-striped" id="discharge-cost-preview">
                        <thead><tr><th>خدمت</th><th>تعداد</th><th>قیمت واحد</th><th>مبلغ</th></tr></thead>
                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="4">
                                خدمتی برای این پذیرش ثبت نشده است.
                                <?= Html::a('افزودن خدمت', ['/admission-service/create', 'admission_id' => $selectedId]) ?>
                            </td></tr>
                        <?php endif; ?>
                        <?php foreach ($items as $item): ?>
                            <?php $amount = (int) $item->quantity * (int) $item->unit_price; $total += $amount; ?>
                            <tr>
                                <td><?= Html::encode($item->service?->title ?? 'خدمت نامشخص') ?></td>
                                <td><?= Html::encode($item->quantity) ?></td>
                                <td><?= number_format((int) $item->unit_price) ?></td>
                                <td><?= number_format($amount) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot><tr><th colspan="3">جمع هزینه خدمات</th><td id="discharge-preview-total"><?= number_format($total) ?> تومان</td></tr></tfoot>
                    </table>
                </div>
                <h2 class="h5 discharge-section-title mt-4">تأیید و ثبت ترخیص</h2>
                <?php $form = ActiveForm::begin(['id' => 'discharge-form', 'action' => ['create', 'admission_id' => $selectedId], 'options' => ['data-loading-form' => '1', 'data-unsaved-warning' => '1']]); ?>
                <?= $form->errorSummary($model) ?>
                <?= Html::activeHiddenInput($model, 'admission_id') ?>
                <?= $form->field($model, 'description')->textarea(['rows' => 4, 'placeholder' => 'توضیحات ترخیص، در صورت نیاز']) ?>
                <p class="discharge-note">تاریخ ترخیص خودکار ثبت می‌شود و مبلغ نهایی هنگام ثبت دوباره محاسبه خواهد شد.</p>
                <div class="form-actions">
                    <?= Html::submitButton('تأیید و ثبت ترخیص', [
                        'class' => 'btn btn-success',
                        'data-loading-label' => 'در حال ثبت ترخیص…',
                        'data-confirm' => 'ترخیص این پذیرش با هزینه نمایش‌داده‌شده ثبت شود؟',
                    ]) ?>
                    <?= Html::a('بازگشت به پرونده', ['/admission/view', 'id' => $selectedAdmission->id], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        <?php else: ?>
            <?php if ($model->hasErrors()): ?>
                <?= Html::errorSummary($model, ['class' => 'alert alert-danger mt-3']) ?>
            <?php endif; ?>
            <p class="discharge-empty-hint">برای نمایش ریز هزینه و ثبت ترخیص، یک پذیرش بستری را جست‌وجو و انتخاب کنید.</p>
        <?php endif; ?>
        <p class="mt-3 mb-0"><?= Html::a('بازگشت به پذیرش‌ها', ['/admission/index'], ['class' => 'btn btn-outline-secondary']) ?></p>
    </div>
</div>
