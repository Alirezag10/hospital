<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\models\Discharge $model */
$this->title = 'ثبت ترخیص';
$this->params['breadcrumbs'][] = ['label' => 'پذیرش‌ها', 'url' => ['/admission/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="discharge-create form-page" dir="rtl">
    <div class="page-heading">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p>پذیرش را پیدا کنید، هزینه‌ها را بررسی کنید و ترخیص را ثبت کنید.</p>
        </div>
    </div>
    <?= $this->render('_form', [
        'model' => $model,
        'selectedAdmission' => $selectedAdmission,
        'hasOpenAdmissions' => $hasOpenAdmissions,
        'items' => $items,
    ]) ?>
</div>
