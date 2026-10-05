<?php

use app\models\Admission;
use app\models\Discharge;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Discharge $model */

$admissions = ArrayHelper::map(
    Admission::find()->with('patient')->where(['status' => 'admitted'])
        ->andWhere(['not in', 'id', Discharge::find()->select('admission_id')])
        ->orderBy(['id' => SORT_DESC])->all(),
    'id',
    static function (Admission $admission): string {
        $patient = $admission->patient;
        return '#' . $admission->id . ' — '
            . ($patient ? $patient->first_name . ' ' . $patient->last_name : 'بیمار نامشخص');
    }
);
$selectedId = filter_var($model->admission_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$selectedAdmission = $selectedId === false ? null : Admission::findOne(['id' => $selectedId]);
$isOpen = $selectedAdmission !== null && $selectedAdmission->status === 'admitted'
    && $selectedAdmission->discharge === null;
$items = $isOpen ? $selectedAdmission->getAdmissionServices()->with('service')->orderBy(['id' => SORT_ASC])->all() : [];
$total = 0;
?>
<div class="discharge-form card" dir="rtl">
    <div class="card-body">
        <h2 class="h5">۱. انتخاب پذیرش و بررسی هزینه</h2>
        <?php if (empty($admissions)): ?>
            <div class="alert alert-info">پذیرش بستری و ترخیص‌نشده‌ای برای انتخاب وجود ندارد.</div>
        <?php endif; ?>
        <?= Html::beginForm(['create'], 'get', ['id' => 'discharge-preview-form']) ?>
        <?= Html::label('پذیرش بیمار', 'discharge-preview-admission', ['class' => 'form-label']) ?>
        <?= Html::dropDownList('admission_id', $isOpen ? $selectedId : null, $admissions, [
            'id' => 'discharge-preview-admission', 'class' => 'form-select mb-3',
            'prompt' => 'پذیرش بیمار را انتخاب کنید', 'required' => true,
        ]) ?>
        <?= Html::submitButton('مشاهده ریز هزینه', ['class' => 'btn btn-outline-primary', 'disabled' => empty($admissions)]) ?>
        <?= Html::endForm() ?>

        <?php if ($isOpen): ?>
            <div class="mt-4">
                <h2 class="h5">ریز خدمات پذیرش #<?= Html::encode($selectedAdmission->id) ?></h2>
                <p>بیمار: <?= Html::encode($selectedAdmission->patient->first_name . ' ' . $selectedAdmission->patient->last_name) ?></p>
                <div class="table-responsive">
                    <table class="table table-striped" id="discharge-cost-preview">
                        <thead><tr><th>خدمت</th><th>تعداد</th><th>قیمت واحد (تومان)</th><th>مبلغ (تومان)</th></tr></thead>
                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="4">خدمتی برای این پذیرش ثبت نشده است.</td></tr>
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
                        <tfoot><tr><th colspan="3">جمع هزینه خدمات (تومان)</th><td id="discharge-preview-total"><?= number_format($total) ?></td></tr></tfoot>
                    </table>
                </div>
                <h2 class="h5 mt-4">۲. تأیید و ثبت ترخیص</h2>
                <?php $form = ActiveForm::begin(['id' => 'discharge-form', 'action' => ['create']]); ?>
                <?= $form->errorSummary($model) ?>
                <?= Html::activeHiddenInput($model, 'admission_id') ?>
                <?= $form->field($model, 'description')->textarea(['rows' => 4, 'placeholder' => 'توضیحات ترخیص، در صورت نیاز']) ?>
                <p class="text-muted">تاریخ ترخیص خودکار ثبت می‌شود. مبلغ هنگام ثبت دوباره از خدمات محاسبه می‌شود. پس از ثبت، ترخیص قابل ویرایش یا حذف نیست.</p>
                <?= Html::submitButton('تأیید و ثبت ترخیص', [
                    'class' => 'btn btn-success',
                    'data-confirm' => 'ترخیص این پذیرش با هزینه نمایش‌داده‌شده ثبت شود؟',
                ]) ?>
                <?= Html::a('بازگشت به پرونده', ['/admission/summary', 'id' => $selectedAdmission->id], ['class' => 'btn btn-outline-secondary']) ?>
                <?php ActiveForm::end(); ?>
            </div>
        <?php else: ?>
            <?php if ($model->hasErrors()): ?>
                <?= Html::errorSummary($model, ['class' => 'alert alert-danger mt-3']) ?>
            <?php endif; ?>
            <p class="text-muted mt-3">برای نمایش مبلغ و ثبت ترخیص، ابتدا پذیرش را انتخاب کنید و ریز هزینه را ببینید.</p>
        <?php endif; ?>
        <p class="mt-3 mb-0"><?= Html::a('بازگشت به ترخیص‌ها', ['index'], ['class' => 'btn btn-outline-secondary']) ?></p>
    </div>
</div>
