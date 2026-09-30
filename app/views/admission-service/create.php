<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\models\AdmissionService $model */
$this->title = 'ثبت خدمت برای پذیرش';
$this->params['breadcrumbs'][] = ['label' => 'خدمات پذیرش', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admission-service-create" dir="rtl">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
