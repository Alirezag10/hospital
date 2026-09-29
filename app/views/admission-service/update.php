<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AdmissionService $model */

$this->title = 'Update Admission Service: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Admission Services', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="admission-service-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
