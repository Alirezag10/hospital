<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\PrescriptionItem;
$this->title=$model->isNewRecord?'افزودن دارو یا مصرفی':'ویرایش قلم';
$this->params['breadcrumbs'][]=['label'=>'دارو و مصرفی','url'=>['index']];$this->params['breadcrumbs'][]=$this->title;
?>
<div class="form-page"><div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>تغییر تعرفه فقط بر انتخاب‌های بعدی اثر می‌گذارد؛ قیمت نسخه‌های ثبت‌شده حفظ می‌شود.</p></div></div>
<div class="card"><div class="card-body"><?php $form=ActiveForm::begin(['enableClientValidation'=>false,'options'=>['data-loading-form'=>'1','data-unsaved-warning'=>'1']]); ?>
<?= $form->errorSummary($model) ?><div class="row g-3">
<div class="col-md-6"><?= $form->field($model,'title')->textInput(['maxlength'=>150]) ?></div>
<div class="col-md-6"><?= $form->field($model,'kind')->dropDownList(PrescriptionItem::kindLabels()) ?></div>
<div class="col-md-6"><?= $form->field($model,'unit_price')->textInput(['inputmode'=>'numeric','dir'=>'ltr']) ?></div>
<div class="col-md-6"><?= $form->field($model,'active')->dropDownList([1=>'فعال',0=>'غیرفعال']) ?></div>
</div><div class="form-actions"><?= Html::submitButton('ذخیره قلم',['class'=>'btn btn-primary']) ?><?= Html::a('انصراف',['index'],['class'=>'btn btn-outline-secondary']) ?></div>
<?php ActiveForm::end(); ?></div></div></div>
