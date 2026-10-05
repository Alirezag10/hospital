<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Ward $model */

$this->title = 'ویرایش بخش: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'بخش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'ویرایش';
?>
<div class="ward-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
