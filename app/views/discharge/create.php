<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Discharge $model */

$this->title = 'Create Discharge';
$this->params['breadcrumbs'][] = ['label' => 'Discharges', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="discharge-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
