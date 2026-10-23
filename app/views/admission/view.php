<?php

use yii\helpers\Html;
use app\components\ServiceAmount;
use app\components\Access;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */

$patient = $model->patient;
$this->title = 'پرونده پذیرش ' . ($patient ? $patient->first_name . ' ' . $patient->last_name : 'بیمار');
$discharge = $model->discharge;
$isOpen = $model->status === 'admitted' && $discharge === null;
$this->params['breadcrumbs'][] = ['label'=>'پذیرش‌ها','url'=>['index']];
$this->params['breadcrumbs'][] = $this->title;
$total=$bill['total'];
$recordedAmount=$discharge===null?null:ServiceAmount::fromDatabase($discharge->total_amount);
$statusLabels=['admitted'=>'بستری','discharged'=>'ترخیص‌شده'];
?>

<div class="admission-summary" dir="rtl">

    <div class="page-heading">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p>اطلاعات بیمار، وضعیت بستری و ریز هزینه‌های پرونده.</p>
        </div>
        <span class="status-badge <?= $isOpen ? 'status-admitted' : 'status-discharged' ?>">
            <?= Html::encode($isOpen && $model->ready_at !== null ? 'آمادهٔ ترخیص' : ($statusLabels[$model->status] ?? $model->status)) ?>
        </span>
    </div>

    <div class="form-actions admission-actions">
        <?php if ($canSeeClinical): ?>
            <?= Html::a('چاپ نسخه و پرونده درمانی', ['/clinical/print', 'admission_id' => $model->id], ['class' => 'btn btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener']) ?>
        <?php else: ?>
            <?= Html::button('چاپ پرونده', ['class' => 'btn btn-outline-secondary', 'data-print-trigger' => '1']) ?>
        <?php endif; ?>
        <?php if($discharge!==null && Access::allows('/discharge/receipt')): ?><?= Html::a('چاپ رسید ترخیص',['/discharge/receipt','admission_id'=>$model->id],['class'=>'btn btn-outline-primary','target'=>'_blank','rel'=>'noopener']) ?><?php endif; ?>
        <?php if ($isOpen): ?>
            <?php if ($canTreat): ?>
            <?= Html::a('ثبت نسخه، دارو و مصرفی', ['/clinical/create', 'admission_id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a($this->render('../layouts/_icon', ['name' => 'service']) . 'افزودن خدمت',
                ['/admission-service/create', 'admission_id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
            <?php endif; ?>
            <?php if (Access::allows('/discharge/create')): ?>
            <?= Html::a($this->render('../layouts/_icon', ['name' => 'discharge']) . 'مشاهده هزینه و ترخیص',
                ['/discharge/create', 'admission_id' => $model->id], ['class' => 'btn btn-success']) ?>
            <?php endif; ?>
        <?php endif; ?>
        <?= Html::a('بازگشت به پذیرش‌ها', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php if ($isOpen): ?>
    <section class="alert <?= $model->ready_at !== null ? 'alert-success' : 'alert-info' ?> print-hidden" aria-label="تأیید پایان درمان">
        <strong><?= $model->ready_at !== null ? 'پایان درمان تأیید شده است.' : 'در انتظار تأیید پایان درمان توسط پزشک' ?></strong>
        <p class="mb-2"><?= $model->ready_at !== null ? 'پرونده در صف ترخیص است. ثبت یا اصلاح نسخه و خدمات، تأیید را لغو می‌کند.' : 'پس از تکمیل نسخه و خدمات، پزشک پرونده را برای ترخیص تأیید می‌کند.' ?></p>
        <?php if (Access::allows('/admission/ready')): ?>
            <?= Html::beginForm(['ready','id'=>$model->id],'post',['class'=>'form-actions mt-0','data-loading-form'=>'1']) ?>
            <?= Html::hiddenInput('ready',$model->ready_at === null ? '1' : '0') ?>
            <?= Html::hiddenInput('treatment_version',$model->treatment_version) ?>
            <?= Html::submitButton($model->ready_at === null ? 'تأیید پایان درمان و ارسال به ترخیص' : 'لغو تأیید پایان درمان',[
                'class'=>'btn '.($model->ready_at === null ? 'btn-success' : 'btn-outline-secondary'),
                'data-confirm'=>$model->ready_at === null ? 'نسخه و خدمات این پرونده کامل است و بیمار برای ترخیص تأیید می‌شود؟' : 'تأیید پایان درمان لغو شود؟',
                'data-loading-label'=>'در حال ثبت…',
            ]) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </section>
    <?php endif; ?>
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
                <dt>تاریخ پذیرش</dt><dd><span dir="ltr"><?= Html::encode(Yii::$app->formatter->asDatetime($model->admission_date)) ?></span></dd>
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
                    <div><div class="timeline-heading"><strong><?= Html::encode($event['title']) ?></strong><time dir="ltr"><?= Html::encode(Yii::$app->formatter->asDatetime($event['date'])) ?></time></div>
                    <p><?= Html::encode($event['detail']) ?></p></div>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <?php if ($canBill): ?>
    <h2>خدمات</h2>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>خدمت</th>
                    <th>تعداد</th>
                    <th>قیمت واحد (تومان)</th>
                    <th>مبلغ (تومان)</th><?php if($canSeeClinical): ?><th class="print-hidden">عملیات</th><?php endif; ?>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="<?= $canSeeClinical?5:4 ?>">
                            خدمتی برای این پذیرش ثبت نشده است.
                            <?php if ($isOpen && $canTreat): ?>
                                <?= Html::a('افزودن خدمت', ['/admission-service/create', 'admission_id' => $model->id]) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($items as $item): ?>
                    <?php
                    $amount = ServiceAmount::line($item->quantity, $item->unit_price);

                    ?>

                    <tr>
                        <td>
                            <?= Html::encode(
                                $item->service?->title
                                ?? 'خدمت نامشخص'
                            ) ?>
                        </td>
                        <td>
                            <?= Yii::$app->formatter->asInteger($item->quantity) ?>
                        </td>
                        <td>
                            <?= Yii::$app->formatter->asInteger(
                                (int) $item->unit_price
                            ) ?>
                        </td>
                        <td>
                            <?= $amount === null ? 'بیش از حد مجاز' : Yii::$app->formatter->asInteger($amount) ?>
                        </td>
                        <?php if($canSeeClinical): ?><td class="print-hidden"><div class="record-actions">
                            <?php if($isOpen && $canTreat): ?><?= Html::a('اصلاح',['/admission-service/update','id'=>$item->id],['class'=>'btn btn-sm btn-outline-primary']) ?><?php endif; ?>
                            <?= Html::a('سابقه',['/record-change/index','type'=>'admission_service','id'=>$item->id],['class'=>'btn btn-sm btn-outline-secondary']) ?>
                        </div></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><th colspan="3">جمع خدمات فعلی</th><td><?= $bill['services'] === null ? 'بیش از حد مجاز' : Yii::$app->formatter->asInteger($bill['services']) . ' تومان' ?></td><?php if($canSeeClinical): ?><td class="print-hidden"></td><?php endif; ?></tr>
            </tfoot>
        </table>
    </div>

    <?php if ($canSeeClinical): ?>
        <?= $this->render('/clinical/_records', ['prescriptions'=>$prescriptions,'showActions'=>true,'canCorrect'=>$isOpen && $canTreat]) ?>
    <?php else: ?>
        <?= $this->render('/clinical/_costs', ['costItems'=>$costItems]) ?>
    <?php endif; ?>
    <div class="clinical-overall-total"><span>جمع خدمات، دارو و لوازم مصرفی</span><strong><?= $total === null ? 'بیش از حد مجاز' : Yii::$app->formatter->asInteger($total) . ' تومان' ?></strong></div>
    <?php endif; ?>

    <h2>ترخیص</h2>

    <?php if ($discharge !== null): ?>
        <p>
            تاریخ ترخیص:
            <span dir="ltr"><?= Html::encode(Yii::$app->formatter->asDatetime($discharge->discharge_date)) ?></span>
            <br>
            <?php if ($canBill): ?>مبلغ نهایی ثبت‌شده هنگام ترخیص:
            <strong>
                <?= $recordedAmount === null ? 'بیش از حد مجاز' : Yii::$app->formatter->asInteger($recordedAmount) . ' تومان' ?>
            </strong>
            <br><?php endif; ?>
            توضیحات:
            <?= Html::encode(
                $discharge->description ?: 'ندارد'
            ) ?>
        </p>

        <?php if ($canBill && $total !== ServiceAmount::fromDatabase($discharge->total_amount)): ?>
            <div class="alert alert-warning">
                جمع خدمات فعلی با مبلغ ثبت‌شده هنگام ترخیص
                متفاوت است؛ خدمات این پذیرش باید بررسی شوند.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p>این پذیرش هنوز ترخیص نشده است.</p>
    <?php endif; ?>

</div>
