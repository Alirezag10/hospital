<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="card mb-3"><div class="card-body">
    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index'], 'enableClientValidation' => false]); ?>
    <div class="row">
        <div class="col-md-6"><?= $form->field($model, 'name')->textInput(['placeholder' => 'نام یا نام خانوادگی']) ?></div>
        <div class="col-md-6"><?= $form->field($model, 'national_code')->textInput(['dir' => 'ltr']) ?></div>
    </div>
    <?= $form->errorSummary($model) ?>
    <?= Html::submitButton('جست‌وجو', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('پاک کردن جست‌وجو', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?php ActiveForm::end(); ?>
</div></div>
