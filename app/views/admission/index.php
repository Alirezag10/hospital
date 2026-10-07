<?php
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = 'پذیرش‌ها';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p>وضعیت پذیرش‌ها و خلاصهٔ پرونده‌ها را بررسی کنید.</p></div>
    <?= Html::a('ثبت پذیرش', ['create'], ['class' => 'btn btn-primary']) ?>
</div>
<?= $this->render('_search', ['model' => $searchModel]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin} تا {end} از {totalCount} پذیرش',
    'emptyText' => 'پذیرشی مطابق جست‌وجوی شما پیدا نشد.',
    'columns' => [
        'id',
        ['label' => 'بیمار', 'value' => static function ($model) {
            return $model->patient->first_name . ' ' . $model->patient->last_name;
        }],
        ['attribute' => 'admission_date', 'contentOptions' => ['dir' => 'ltr']],
        'doctor_name', 'ward',
        ['attribute' => 'status', 'format' => 'raw', 'value' => static function ($model) {
            $isAdmitted = $model->status === 'admitted';
            return Html::tag('span', $isAdmitted ? 'بستری' : 'ترخیص‌شده', [
                'class' => 'status-badge ' . ($isAdmitted ? 'status-admitted' : 'status-discharged'),
            ]);
        }],
        ['label' => 'عملیات', 'format' => 'raw', 'value' => static function ($model) {
            return Html::a('خلاصه پرونده', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']);
        }],
    ],
]) ?>
</div>
