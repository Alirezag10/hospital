<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */

$patient = $model->patient;
$this->title = 'پرونده پذیرش ' . ($patient ? $patient->first_name . ' ' . $patient->last_name : 'بیمار');
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
$timeline = [['date' => $model->admission_date, 'title' => 'ثبت پذیرش', 'detail' => 'پذیرش بیمار در ' . $model->ward . ' توسط ' . $model->doctor_name]];
foreach ($items as $item) {
    $timeline[] = ['date' => $item->created_at, 'title' => 'ثبت خدمت', 'detail' => ($item->service?->title ?? 'خدمت') . ' — تعداد ' . $item->quantity];
}
if ($discharge !== null) {
    $timeline[] = ['date' => $discharge->discharge_date, 'title' => 'ثبت ترخیص', 'detail' => 'مبلغ نهایی ' . number_format((int) $discharge->total_amount) . ' تومان'];
}
usort($timeline, static fn($a, $b) => strcmp((string) $a['date'], (string) $b['date']));
?>

<div class="admission-summary" dir="rtl">

    <div class="page-heading">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p>اطلاعات بیمار، وضعیت بستری و ریز هزینه‌های پرونده.</p>
        </div>
        <span class="status-badge <?= $isOpen ? 'status-admitted' : 'status-discharged' ?>">
            <?= Html::encode($statusLabels[$model->status] ?? $model->status) ?>
        </span>
    </div>

    <div class="form-actions admission-actions">
        <?= Html::button($discharge !== null ? 'چاپ رسید ترخیص' : 'چاپ پرونده', ['class' => 'btn btn-outline-secondary', 'data-print-trigger' => '1']) ?>
        <?php if ($isOpen): ?>
            <?= Html::a($this->render('../layouts/_icon', ['name' => 'service']) . 'افزودن خدمت',
                ['/admission-service/create', 'admission_id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a($this->render('../layouts/_icon', ['name' => 'discharge']) . 'مشاهده هزینه و ترخیص',
                ['/discharge/create', 'admission_id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
        <?= Html::a('بازگشت به پذیرش‌ها', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="admission-details">
        <section>
            <h2>بیمار</h2>
            <?php if ($patient !== null): ?>
                <dl class="detail-list">
                    <dt>نام</dt><dd><?= Html::encode($patient->first_name . ' ' . $patient->last_name) ?></dd>
                    <dt>کد ملی</dt><dd dir="ltr"><?= Html::encode($patient->national_code) ?></dd>
                    <dt>موبایل</dt><dd dir="ltr"><?= Html::encode($patient->mobile) ?></dd>
                </dl>
            <?php else: ?>
                <p>اطلاعات بیمار پیدا نشد.</p>
            <?php endif; ?>
        </section>
        <section>
            <h2>پذیرش</h2>
            <dl class="detail-list">
                <dt>تاریخ پذیرش</dt><dd><span dir="ltr"><?= Html::encode($model->admission_date) ?></span></dd>
                <dt>بخش</dt><dd><?= Html::encode($model->ward) ?></dd>
                <dt>پزشک</dt><dd><?= Html::encode($model->doctor_name) ?></dd>
            </dl>
        </section>
    </div>

    <section class="admission-timeline" aria-labelledby="timeline-title">
        <h2 id="timeline-title">روند پرونده</h2>
        <ol class="timeline-list">
            <?php foreach ($timeline as $event): ?>
                <li class="timeline-item"><span class="timeline-marker" aria-hidden="true"></span>
                    <div><div class="timeline-heading"><strong><?= Html::encode($event['title']) ?></strong><time dir="ltr"><?= Html::encode($event['date']) ?></time></div>
                    <p><?= Html::encode($event['detail']) ?></p></div>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

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
                            <?php if ($isOpen): ?>
                                <?= Html::a('افزودن خدمت', ['/admission-service/create', 'admission_id' => $model->id]) ?>
                            <?php endif; ?>
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
            <tfoot>
                <tr><th colspan="3">جمع خدمات فعلی</th><td><?= number_format($total) ?> تومان</td></tr>
            </tfoot>
        </table>
    </div>

    <h2>ترخیص</h2>

    <?php if ($discharge !== null): ?>
        <p>
            تاریخ ترخیص:
            <span dir="ltr"><?= Html::encode($discharge->discharge_date) ?></span>
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
