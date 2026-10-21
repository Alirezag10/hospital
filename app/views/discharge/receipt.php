<?php
use yii\helpers\Html;
$this->title='رسید ترخیص '.$snapshot['patient_name'];
$money=static fn($n)=>$n === null ? 'بیش از حد مجاز' : Yii::$app->formatter->asInteger($n).' تومان';
$groups=[['خدمات',$snapshot['services']],['دارو و لوازم مصرفی',$snapshot['items']]];
?>
<div class="print-toolbar"><span>پیش‌نمایش رسید مالی ترخیص</span><div class="form-actions mt-0">
<?= Html::button('چاپ رسید',['class'=>'btn btn-primary','data-print-trigger'=>'1']) ?><?= Html::a('بازگشت به پرونده',['/admission/view','id'=>$snapshot['admission_id']],['class'=>'btn btn-outline-secondary']) ?></div></div>
<header class="clinical-print-header"><div><p><?= Html::encode($snapshot['hospital']) ?></p><h1>رسید هزینه‌های ترخیص</h1></div><strong>شماره رسید: <?= Yii::$app->formatter->asInteger($snapshot['receipt_id']) ?></strong></header>
<dl class="clinical-print-patient">
<?php foreach (['patient_name'=>'نام بیمار','national_code'=>'کد ملی','doctor'=>'پزشک','ward'=>'بخش'] as $key=>$label): ?>
<div><dt><?= $label ?></dt><dd><?= Html::encode($snapshot[$key]) ?></dd></div>
<?php endforeach; ?>
<div><dt>شماره پذیرش</dt><dd><?= Yii::$app->formatter->asInteger($snapshot['admission_id']) ?></dd></div>
<?php foreach (['admission_date'=>'تاریخ پذیرش','discharge_date'=>'تاریخ ترخیص'] as $key=>$label): ?>
<div><dt><?= $label ?></dt><dd><?= Html::encode(Yii::$app->formatter->asDatetime($snapshot[$key])) ?></dd></div>
<?php endforeach; ?>
</dl>
<?php foreach ($groups as [$label,$rows]): ?>
<h2 class="h5"><?= $label ?></h2><table class="table receipt-table"><thead><tr><th>عنوان</th><th>تعداد</th><th>قیمت واحد</th><th>مبلغ</th></tr></thead><tbody>
<?php if (!$rows): ?><tr><td colspan="4">موردی ثبت نشده است.</td></tr><?php endif; ?>
<?php foreach ($rows as $row): ?><tr><td><?= Html::encode($row['title']) ?></td><td><?= Yii::$app->formatter->asInteger($row['quantity']) ?></td><td><?= $money($row['unit_price']) ?></td><td><?= $money($row['amount']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php endforeach; ?>
<dl class="receipt-totals"><div><dt>جمع خدمات</dt><dd><?= $money($snapshot['bill']['services']) ?></dd></div><div><dt>جمع دارو</dt><dd><?= $money($snapshot['bill']['medicine']) ?></dd></div><div><dt>جمع لوازم مصرفی</dt><dd><?= $money($snapshot['bill']['supply']) ?></dd></div><div class="receipt-final"><dt>مبلغ قطعی ثبت‌شده هنگام ترخیص</dt><dd><?= $money($snapshot['final']) ?></dd></div></dl>
<?php if ($snapshot['bill']['total'] !== $snapshot['final']): ?><p class="alert alert-warning">جمع ردیف‌های تثبیت‌شده با مبلغ قطعی ترخیص متفاوت است؛ مبلغ قطعی بالا از رکورد ترخیص حفظ شده است.</p><?php endif; ?>
<footer class="clinical-print-signature"><span>امضای مسئول ترخیص</span><span>امضای بیمار یا همراه</span></footer>
