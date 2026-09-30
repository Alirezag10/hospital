<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\models\Discharge $model */
$this->title = 'ثبت ترخیص';
$this->params['breadcrumbs'][] = ['label' => 'ترخیص‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="discharge-create" dir="rtl">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
