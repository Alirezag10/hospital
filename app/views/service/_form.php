<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/** @var yii\web\View $this */
/** @var app\models\Service $model */
?>
<div class="service-form card" dir="rtl">
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->errorSummary($model) ?>
        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'مثلاً آزمایش خون']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'price')->label('قیمت (تومان)')->input('number', ['min' => 0, 'step' => 1, 'dir' => 'ltr']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'active')->label('وضعیت خدمت')->dropDownList([1 => 'فعال', 0 => 'غیرفعال']) ?>
            </div>
        </div>
        <p class="text-muted">خدمات غیرفعال برای ثبت خدمت جدید قابل انتخاب نیستند. قیمت خدمات ثبت‌شدهٔ قبلی حفظ می‌شود.</p>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::submitButton($model->isNewRecord ? 'تعریف خدمت' : 'ذخیره تغییرات', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('انصراف', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
