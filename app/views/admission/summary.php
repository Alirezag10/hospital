<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */

$this->title = 'خلاصه پرونده پذیرش #' . $model->id;

$items = $model->admissionServices;
$total = 0;
?>

<div class="admission-summary">
    <h1><?= Html::encode($this->title) ?></h1>

    <h2>بیمار</h2>
    <p>
        نام:
        <?= Html::encode($model->patient->first_name . ' ' . $model->patient->last_name) ?>
        <br>
        کد ملی: <?= Html::encode($model->patient->national_code) ?>
        <br>
        موبایل: <?= Html::encode($model->patient->mobile) ?>
    </p>

    <h2>پذیرش</h2>
    <p>
        تاریخ پذیرش: <?= Html::encode($model->admission_date) ?>
        <br>
        بخش: <?= Html::encode($model->ward) ?>
        <br>
        پزشک: <?= Html::encode($model->doctor_name) ?>
        <br>
        وضعیت:
        <?= $model->status === 'discharged' ? 'ترخیص‌شده' : 'پذیرش‌شده' ?>
    </p>

    <h2>خدمات</h2>
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
            <?php foreach ($items as $item): ?>
                <?php
                $amount = (int) $item->quantity * (int) $item->unit_price;
                $total += $amount;
                ?>
                <tr>
                    <td><?= Html::encode($item->service->title) ?></td>
                    <td><?= Html::encode($item->quantity) ?></td>
                    <td><?= number_format((int) $item->unit_price) ?></td>
                    <td><?= number_format($amount) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><strong>جمع خدمات: <?= number_format($total) ?> تومان</strong></p>

    <h2>ترخیص</h2>
    <?php if ($model->discharge !== null): ?>
        <p>
            تاریخ ترخیص: <?= Html::encode($model->discharge->discharge_date) ?>
            <br>
            مبلغ ثبت‌شده هنگام ترخیص:
            <?= number_format((int) $model->discharge->total_amount) ?> تومان
            <br>
            توضیحات:
            <?= Html::encode($model->discharge->description ?: 'ندارد') ?>
        </p>
    <?php else: ?>
        <p>این پذیرش هنوز ترخیص نشده است.</p>
    <?php endif; ?>
</div>