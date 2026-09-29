<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Discharge $model */

$this->title = 'Update Discharge: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Discharges', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="discharge-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
