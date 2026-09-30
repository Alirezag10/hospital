<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Doctor $model */

$this->title = 'Create Doctor';
$this->params['breadcrumbs'][] = ['label' => 'Doctors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="doctor-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
