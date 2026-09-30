<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\models\Service $model */
$this->title = 'تعریف خدمت جدید';
$this->params['breadcrumbs'][] = ['label' => 'خدمات', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-create" dir="rtl">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
