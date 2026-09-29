<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AdmissionService $model */

$this->title = 'Create Admission Service';
$this->params['breadcrumbs'][] = ['label' => 'Admission Services', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admission-service-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
