<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */

$this->title = 'ویرایش پذیرش #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'پذیرش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'پذیرش #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'ویرایش';
?>
<div class="admission-update" dir="rtl">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
