<?php
use app\models\PrescriptionItem;
use yii\helpers\Html;
use yii\helpers\Url;
$catalogId=Html::getInputId($item,"[$index]medical_item_id");
$titleId=Html::getInputId($item,"[$index]title");
$original=$original??null;
$fields = [
    'kind' => ['نوع قلم', 'clinical-field-kind'], 'title' => ['نام دارو یا وسیله', 'clinical-field-title'],
    'quantity' => ['تعداد', 'clinical-field-number'], 'unit_price' => ['قیمت واحد (تومان)', 'clinical-field-price'],
    'dosage' => ['مقدار و دفعات مصرف', 'clinical-field-dosage'], 'instructions' => ['دستور مصرف یا توضیح', 'clinical-field-instructions'],
];
?>
<?= Html::beginTag('fieldset',['class'=>'prescription-item','data-prescription-item'=>'1','data-original-id'=>$original?->medical_item_id??'',
    'data-original-price'=>$original?->unit_price??'','data-original-title'=>$original?->title??'','data-original-kind'=>$original?->kind??'']) ?>
    <legend>قلم درمانی</legend>
    <?= Html::activeHiddenInput($item,"[$index]medical_item_id",['data-line-medical_item_id'=>'1']) ?>
    <?= Html::hiddenInput("PrescriptionItem[$index][item_id]",$item->sourceItemId??($item->isNewRecord?'':$item->id)) ?>
    <div class="prescription-item-grid">
        <?php foreach ($fields as $attribute => [$label, $class]): ?>
            <div class="clinical-field <?= $class ?><?= $item->hasErrors($attribute) ? ' has-error' : '' ?>">
                <?= Html::label($label, Html::getInputId($item, "[$index]$attribute"), ['class' => 'form-label']) ?>
                <?php if ($attribute === 'kind'): ?>
                    <?= Html::activeDropDownList($item, "[$index]$attribute", PrescriptionItem::kindLabels(), ['class' => 'form-select']) ?>
                <?php elseif($attribute==='title'): ?>
                    <div class="catalog-search position-relative" data-catalog-url="<?= Html::encode(Url::to(['/medical-item/search'])) ?>">
                    <?= Html::activeTextInput($item,"[$index]title",['class'=>'form-control','maxlength'=>150,'autocomplete'=>'off','placeholder'=>'نام قلم را جست‌وجو کنید','data-line-title'=>'1','data-catalog-search'=>'1','readonly'=>(bool)$item->medical_item_id,'role'=>'combobox','aria-expanded'=>'false','aria-autocomplete'=>'list','aria-controls'=>$titleId.'-results']) ?>
                    <div id="<?= $titleId ?>-results" class="dropdown-menu catalog-results w-100" role="listbox" hidden></div>
                    </div>
                    <div class="catalog-status" aria-live="polite"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary catalog-change" data-catalog-clear <?= $item->medical_item_id?'':'hidden' ?>>تغییر انتخاب</button>
                    <?= Html::error($item,'medical_item_id',['class'=>'help-block']) ?>
                <?php else: ?>
                    <?= Html::activeTextInput($item, "[$index]$attribute", [
                        'class' => 'form-control', 'maxlength' => $attribute === 'title' ? 150 : ($attribute === 'instructions' ? 500 : 100),
                        'inputmode' => in_array($attribute, ['quantity', 'unit_price'], true) ? 'numeric' : 'text',
                        'dir' => in_array($attribute, ['quantity', 'unit_price'], true) ? 'ltr' : 'rtl',
                        'data-line-' . $attribute => '1',
                        'readonly' => $attribute === 'unit_price',
                    ]) ?>
                <?php endif; ?>
                <?= Html::error($item, $attribute, ['class' => 'help-block']) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="prescription-item-footer"><span>مبلغ ردیف: <strong data-line-total>۰ تومان</strong></span>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-remove-prescription-item>حذف ردیف</button></div>
</fieldset>
