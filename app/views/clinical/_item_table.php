<?php
use app\components\ServiceAmount;
use app\models\PrescriptionItem;
use yii\helpers\Html;
$showInstructions = $showInstructions ?? false;
?>
<div class="table-responsive clinical-items-table">
<table class="table table-striped">
    <thead><tr><th scope="col">نوع / عنوان</th><th scope="col">تعداد</th>
        <?php if ($showInstructions): ?><th scope="col">دستور مصرف</th><?php endif; ?>
        <th scope="col">قیمت واحد (تومان)</th><th scope="col">مبلغ (تومان)</th></tr></thead>
    <tbody>
        <?php foreach ($items as $item): $amount = ServiceAmount::line($item->quantity, $item->unit_price); ?>
            <tr><td><small><?= Html::encode(PrescriptionItem::kindLabels()[$item->kind]) ?></small><strong><?= Html::encode($item->title) ?></strong></td>
                <td><?= Yii::$app->formatter->asInteger($item->quantity) ?></td>
                <?php if ($showInstructions): ?><td class="clinical-instructions"><?= Html::encode($item->dosage ?: '—') ?><br><?= Html::encode($item->instructions) ?></td><?php endif; ?>
                <td><?= Yii::$app->formatter->asInteger($item->unit_price) ?></td><td><?= $amount === null ? 'بیش از حد مجاز' : Yii::$app->formatter->asInteger($amount) ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table></div>
