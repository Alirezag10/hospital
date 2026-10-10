<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Patient $model */

$this->title = 'ثبت بیمار جدید';
$this->params['breadcrumbs'][] = ['label' => 'بیماران', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="patient-create form-page" dir="rtl">
    <div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>اطلاعات پایه را وارد کنید؛ موارد ستاره‌دار الزامی هستند.</p></div></div>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
