<?php

use app\models\Admission;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\AdmissionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'پذیرش‌ها';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="admission-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('ثبت پذیرش', ['create'], [
            'class' => 'btn btn-success',
        ]) ?>
    </p>
    <?= $this->render('_search', ['model' => $searchModel]) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
            ],
            'id',
            [
                'attribute' => 'patientName',
                'label' => 'بیمار',
                'value' => function ($model) {
                    $patient = $model->patient;

                    return $patient
                        ? $patient->first_name . ' ' . $patient->last_name
                        : '-';
                },
            ],
            [
                'attribute' => 'admission_date',
                'label' => 'تاریخ پذیرش',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'placeholder' => '۱۴۰۵/۰۷/۰۸',
                ],
            ],
            [
                'attribute' => 'doctorName',
                'label' => 'پزشک',
                'value' => function ($model) {
                    return $model->doctor?->name ?? '-';
                },
            ],
            [
                'attribute' => 'wardName',
                'label' => 'بخش',
                'value' => function ($model) {
                    return $model->wardModel?->name ?? '-';
                },
            ],
            [
                'attribute' => 'status',
                'label' => 'وضعیت',
                'filter' => [
                    'admitted' => 'بستری',
                    'discharged' => 'ترخیص‌شده',
                ],
                'value' => function ($model) {
                    return [
                        'admitted' => 'بستری',
                        'discharged' => 'ترخیص‌شده',
                    ][$model->status] ?? $model->status;
                },
            ],
            [
                'attribute' => 'created_at',
                'label' => 'تاریخ ثبت',
                'format' => 'datetime',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'placeholder' => '۱۴۰۵/۰۷/۰۸',
                ],
            ],
            [
                'class' => ActionColumn::class,
                'urlCreator' => function (
                    $action,
                    Admission $model,
                    $key,
                    $index,
                    $column
                ) {
                    return Url::toRoute([
                        $action,
                        'id' => $model->id,
                    ]);
                },
            ],
        ],
    ]) ?>

</div>