<?php
use yii\helpers\Html;
use app\components\ServiceAmount;
$number=static fn($n)=>Yii::$app->formatter->asInteger($n);
?>
<div class="change-snapshot">
<p class="clinical-empty">ویرایش <?= $number($data['revision']??1) ?></p>
<?php if($type==='prescription'): ?>
<h4>تشخیص و شرح وضعیت</h4><p class="clinical-prose"><?= Html::encode($data['diagnosis']??'') ?></p>
<?php if(!empty($data['notes'])): ?><h4>دستورات پزشک</h4><p class="clinical-prose"><?= Html::encode($data['notes']) ?></p><?php endif; ?>
<?php foreach($data['items']??[] as $row): ?><dl class="change-item">
<dt><?= Html::encode($row['title']) ?></dt><dd>تعداد: <?= $number($row['quantity']) ?> · قیمت واحد: <?= $number($row['unit_price']) ?> تومان</dd>
<?php if(!empty($row['dosage'])||!empty($row['instructions'])): ?><dd><?= Html::encode(($row['dosage']??'').' '.($row['instructions']??'')) ?></dd><?php endif; ?>
</dl><?php endforeach; ?>
<?php if(empty($data['items'])): ?><p>قلمی ثبت نشده است.</p><?php endif; ?>
<?php else: ?>
<dl class="detail-list"><dt>خدمت</dt><dd><?= Html::encode($serviceNames[$data['service_id']??0]??'خدمت نامشخص') ?></dd><dt>تعداد</dt><dd><?= $number($data['quantity']??0) ?></dd><dt>قیمت واحد</dt><dd><?= $number($data['unit_price']??0) ?> تومان</dd><dt>مبلغ ردیف</dt><dd><?= ($amount=ServiceAmount::line($data['quantity']??0,$data['unit_price']??0))===null?'بیش از حد مجاز':$number($amount).' تومان' ?></dd></dl>
<?php endif; ?>
</div>
