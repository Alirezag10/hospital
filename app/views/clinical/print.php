<?php
use yii\helpers\Html;
$patient = $admission->patient;
$this->title = 'پرونده درمانی ' . $patient->first_name . ' ' . $patient->last_name;
?>
<div class="print-toolbar">
    <span>پیش‌نمایش چاپ پرونده درمانی</span><div class="form-actions mt-0">
        <?= Html::button('چاپ نسخه و پرونده', ['class' => 'btn btn-primary', 'data-print-trigger' => '1']) ?>
        <?= Html::a('بازگشت به پرونده', ['/admission/view', 'id' => $admission->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>
<header class="clinical-print-header"><div><p><?= Html::encode(Yii::$app->name) ?></p><h1>پرونده درمانی و نسخه پزشک</h1></div>
    <span>شماره پرونده: <?= Yii::$app->formatter->asInteger($admission->id) ?></span></header>
<dl class="clinical-print-patient">
    <div><dt>بیمار</dt><dd><?= Html::encode($patient->first_name . ' ' . $patient->last_name) ?></dd></div>
    <div><dt>کد ملی</dt><dd><bdi><?= Html::encode($patient->national_code) ?></bdi></dd></div>
    <div><dt>پزشک</dt><dd><?= Html::encode($admission->doctor_name) ?></dd></div>
    <div><dt>بخش</dt><dd><?= Html::encode($admission->ward) ?></dd></div>
    <div><dt>تاریخ پذیرش</dt><dd><?= Html::encode(Yii::$app->formatter->asDatetime($admission->admission_date)) ?></dd></div>
</dl>
<?= $this->render('_records', compact('prescriptions')) ?>
<?php if ($services): ?><section><h2>خدمات ثبت‌شده برای پذیرش</h2>
    <table class="table"><thead><tr><th>خدمت</th><th>تعداد</th><th>تاریخ ثبت</th></tr></thead><tbody>
        <?php foreach ($services as $service): ?><tr><td><?= Html::encode($service->service->title) ?></td><td><?= Yii::$app->formatter->asInteger($service->quantity) ?></td><td><?= Html::encode(Yii::$app->formatter->asDatetime($service->created_at)) ?></td></tr><?php endforeach; ?>
    </tbody></table></section><?php endif; ?>
<footer class="clinical-print-signature"><span>نام پزشک: <?= Html::encode($admission->doctor_name) ?></span><span>مهر و امضای پزشک</span></footer>
