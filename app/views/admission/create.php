<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */

$this->title = 'ثبت پذیرش جدید';
$this->params['breadcrumbs'][] = ['label' => 'پذیرش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admission-create" dir="rtl">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
