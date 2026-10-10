<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */
/** @var bool $patientLocked */

$this->title = 'ثبت پذیرش جدید';
$this->params['breadcrumbs'][] = ['label' => 'پذیرش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admission-create form-page" dir="rtl">
    <div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>بیمار، پزشک و بخش را انتخاب کنید تا پروندهٔ بستری باز شود.</p></div></div>
    <?= $this->render('_form', [
        'model' => $model,
        'patientLocked' => $patientLocked,
    ]) ?>
</div>
