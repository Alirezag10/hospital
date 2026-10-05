<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Patient $model */

$this->title = 'اطلاعات بیمار: ' . $model->first_name . ' ' . $model->last_name;
$this->params['breadcrumbs'][] = ['label' => 'بیماران', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="patient-view" dir="rtl">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <?= Html::a('ثبت پذیرش برای این بیمار', ['/admission/create', 'patient_id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ویرایش اطلاعات', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('بازگشت به بیماران', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('حذف بیمار', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-outline-danger',
            'data' => [
                'confirm' => 'اطلاعات این بیمار حذف شود؟',
                'method' => 'post',
            ],
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body table-responsive">
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-striped table-bordered detail-view mb-0'],
                'attributes' => [
                    'id',
                    'first_name',
                    'last_name',
                    'national_code',
                    'mobile',
                    [
                        'attribute' => 'birth_date',
                        'label' => 'تاریخ تولد (شمسی)',
                        'value' => $model->birth_date ?: 'ثبت نشده',
                    ],
                    'created_at',
                ],
            ]) ?>
        </div>
    </div>
</div>
