<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Ward $model */

$this->title = 'تعریف بخش';
$this->params['breadcrumbs'][] = ['label' => 'بخش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ward-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
