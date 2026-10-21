<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title=$model->isNewRecord?'تعریف خدمت جدید':'ویرایش خدمت';
$this->params['breadcrumbs'][]=['label'=>'خدمات و تعرفه‌ها','url'=>['index']];
$this->params['breadcrumbs'][]=$this->title;
?>
<div class="form-page"><div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>تغییر تعرفه فقط برای ثبت‌های جدید است؛ قیمت خدمات قبلی حفظ می‌شود.</p></div></div>
<div class="card"><div class="card-body">
<?php $form=ActiveForm::begin(['options'=>['data-loading-form'=>'1','data-unsaved-warning'=>'1']]); ?>
<?= $form->errorSummary($model) ?>
<fieldset class="form-section"><legend>مشخصات خدمت</legend><div class="row g-3">
<div class="col-md-12"><?= $form->field($model,'title')->textInput(['maxlength'=>150,'placeholder'=>'مثلاً ویزیت']) ?></div>
<div class="col-md-6"><?= $form->field($model,'price')->textInput(['inputmode'=>'numeric','dir'=>'ltr'])->label('تعرفه (تومان)') ?></div>
<div class="col-md-6"><?= $form->field($model,'active')->dropDownList([1=>'فعال',0=>'غیرفعال']) ?></div>
</div></fieldset><div class="form-actions"><?= Html::submitButton('ذخیره خدمت',['class'=>'btn btn-primary','data-loading-label'=>'در حال ذخیره…']) ?><?= Html::a('انصراف',['index'],['class'=>'btn btn-outline-secondary']) ?></div>
<?php ActiveForm::end(); ?></div></div></div>
