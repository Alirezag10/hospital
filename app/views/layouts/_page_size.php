<?php
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
if ($dataProvider->pagination === false) { return; }
?>
<div class="table-page-size" aria-label="تعداد موارد در هر صفحه">
    <?= Html::label('نمایش در هر صفحه', 'page-size-select', ['class' => 'form-label']) ?>
    <?= Html::dropDownList('per-page', $dataProvider->pagination->pageSize, [10 => '۱۰', 25 => '۲۵', 50 => '۵۰'], [
        'id' => 'page-size-select', 'class' => 'form-select', 'data-page-size' => '1', 'aria-label' => 'تعداد ردیف‌های جدول در هر صفحه',
    ]) ?>
</div>
