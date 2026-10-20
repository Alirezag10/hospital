<?php
use app\models\PrescriptionItem;
use yii\helpers\Html;
$fields = [
    'kind' => ['نوع قلم', 'clinical-field-kind'], 'title' => ['نام دارو یا وسیله', 'clinical-field-title'],
    'quantity' => ['تعداد', 'clinical-field-number'], 'unit_price' => ['قیمت واحد (تومان)', 'clinical-field-price'],
    'dosage' => ['مقدار و دفعات مصرف', 'clinical-field-dosage'], 'instructions' => ['دستور مصرف یا توضیح', 'clinical-field-instructions'],
];
?>
<fieldset class="prescription-item" data-prescription-item>
    <legend>قلم درمانی</legend>
    <div class="prescription-item-grid">
        <?php foreach ($fields as $attribute => [$label, $class]): ?>
            <div class="clinical-field <?= $class ?><?= $item->hasErrors($attribute) ? ' has-error' : '' ?>">
                <?= Html::label($label, Html::getInputId($item, "[$index]$attribute"), ['class' => 'form-label']) ?>
                <?php if ($attribute === 'kind'): ?>
                    <?= Html::activeDropDownList($item, "[$index]$attribute", PrescriptionItem::kindLabels(), ['class' => 'form-select']) ?>
                <?php else: ?>
                    <?= Html::activeTextInput($item, "[$index]$attribute", [
                        'class' => 'form-control', 'maxlength' => $attribute === 'title' ? 150 : ($attribute === 'instructions' ? 500 : 100),
                        'inputmode' => in_array($attribute, ['quantity', 'unit_price'], true) ? 'numeric' : 'text',
                        'dir' => in_array($attribute, ['quantity', 'unit_price'], true) ? 'ltr' : 'rtl',
                        'data-line-' . $attribute => '1',
                    ]) ?>
                <?php endif; ?>
                <?= Html::error($item, $attribute, ['class' => 'help-block']) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="prescription-item-footer"><span>مبلغ ردیف: <strong data-line-total>۰ تومان</strong></span>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-remove-prescription-item>حذف ردیف</button></div>
</fieldset>
