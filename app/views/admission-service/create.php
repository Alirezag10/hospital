<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\models\AdmissionService $model */
/** @var bool $admissionLocked */
$this->title = 'ثبت خدمت برای پذیرش';
$this->params['breadcrumbs'][] = ['label' => 'پذیرش‌ها', 'url' => ['/admission/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admission-service-create form-page" dir="rtl">
    <div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>خدمت و تعداد آن را برای یک پذیرش بستری ثبت کنید.</p></div></div>
    <?= $this->render('_form', ['model' => $model, 'admissionLocked' => $admissionLocked]) ?>
</div>
