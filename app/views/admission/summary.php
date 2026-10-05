<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */

$this->title = 'خلاصه پرونده پذیرش #' . $model->id;

$patient = $model->patient;
$doctor = $model->doctor;
$ward = $model->wardModel;
$discharge = $model->discharge;
$isOpen = $model->status === 'admitted' && $discharge === null;
$this->params['breadcrumbs'][] = ['label' => 'پذیرش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$items = $model->getAdmissionServices()
    ->with('service')
    ->orderBy(['id' => SORT_ASC])
    ->all();

$total = 0;

$statusLabels = [
    'admitted' => 'بستری',
    'discharged' => 'ترخیص‌شده',
];
?>

<div class="admission-summary" dir="rtl">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <?= Html::a('مشاهده پذیرش', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a('بازگشت به پذیرش‌ها', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?php if ($isOpen): ?>
            <?= Html::a('افزودن خدمت', ['/admission-service/create', 'admission_id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('مشاهده هزینه و ترخیص', ['/discharge/create', 'admission_id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </div>

    <h2>بیمار</h2>

    <?php if ($patient !== null): ?>
        <p>
            نام:
            <?= Html::encode(
                $patient->first_name . ' ' . $patient->last_name
            ) ?>
            <br>
            کد ملی:
            <?= Html::encode($patient->national_code) ?>
            <br>
            موبایل:
            <?= Html::encode($patient->mobile) ?>
        </p>
    <?php else: ?>
        <p>اطلاعات بیمار پیدا نشد.</p>
    <?php endif; ?>

    <h2>پذیرش</h2>

    <p>
        تاریخ پذیرش:
        <?= Html::encode($model->admission_date) ?>
        <br>
        بخش:
        <?= Html::encode($ward?->name ?? '-') ?>
        <br>
        پزشک:
        <?= Html::encode($doctor?->name ?? '-') ?>
        <br>
        وضعیت:
        <?= Html::encode(
            $statusLabels[$model->status] ?? $model->status
        ) ?>
    </p>

    <h2>خدمات</h2>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>خدمت</th>
                    <th>تعداد</th>
                    <th>قیمت واحد (تومان)</th>
                    <th>مبلغ (تومان)</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="4">
                            خدمتی برای این پذیرش ثبت نشده است.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($items as $item): ?>
                    <?php
                    $amount = (int) $item->quantity
                        * (int) $item->unit_price;

                    $total += $amount;
                    ?>

                    <tr>
                        <td>
                            <?= Html::encode(
                                $item->service?->title
                                ?? 'خدمت نامشخص'
                            ) ?>
                        </td>
                        <td>
                            <?= Html::encode($item->quantity) ?>
                        </td>
                        <td>
                            <?= number_format(
                                (int) $item->unit_price
                            ) ?>
                        </td>
                        <td>
                            <?= number_format($amount) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p>
        <strong>
            جمع خدمات فعلی:
            <?= number_format($total) ?> تومان
        </strong>
    </p>

    <h2>ترخیص</h2>

    <?php if ($discharge !== null): ?>
        <p>
            تاریخ ترخیص:
            <?= Html::encode($discharge->discharge_date) ?>
            <br>
            مبلغ نهایی ثبت‌شده هنگام ترخیص:
            <strong>
                <?= number_format(
                    (int) $discharge->total_amount
                ) ?>
                تومان
            </strong>
            <br>
            توضیحات:
            <?= Html::encode(
                $discharge->description ?: 'ندارد'
            ) ?>
        </p>

        <?php if ($total !== (int) $discharge->total_amount): ?>
            <div class="alert alert-warning">
                جمع خدمات فعلی با مبلغ ثبت‌شده هنگام ترخیص
                متفاوت است؛ خدمات این پذیرش باید بررسی شوند.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p>این پذیرش هنوز ترخیص نشده است.</p>
    <?php endif; ?>

</div>
