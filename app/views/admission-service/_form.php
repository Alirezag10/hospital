<?php
use app\models\Admission;
use app\models\Discharge;
use app\models\Service;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$admissions = ArrayHelper::map(
    Admission::find()->with('patient')->where(['status' => 'admitted'])
        ->andWhere(['not in', 'id', Discharge::find()->select('admission_id')])->all(),
    'id', static function ($admission) {
        return '#' . $admission->id . ' — ' . $admission->patient->first_name . ' ' . $admission->patient->last_name;
    }
);
$services = ArrayHelper::map(Service::find()->where(['active' => 1])->all(), 'id', static function ($service) {
    return $service->title . ' (' . number_format($service->price) . ' تومان)';
});
?>
<div class="card"><div class="card-body">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->errorSummary($model) ?>
    <?= $form->field($model, 'admission_id')->dropDownList($admissions, ['prompt' => 'انتخاب پذیرش']) ?>
    <?= $form->field($model, 'service_id')->dropDownList($services, ['prompt' => 'انتخاب خدمت']) ?>
    <?= $form->field($model, 'quantity')->input('number', ['min' => 1, 'step' => 1]) ?>
    <?= Html::submitButton('ثبت خدمت', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('انصراف', $model->admission_id ? ['/admission/view', 'id' => $model->admission_id] : ['/admission/index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?php ActiveForm::end(); ?>
</div></div>
