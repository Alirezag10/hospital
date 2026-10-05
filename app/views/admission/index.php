<?php
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = 'پذیرش‌ها';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>
<p><?= Html::a('ثبت پذیرش', ['create'], ['class' => 'btn btn-primary']) ?></p>
<div class="table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => 'نمایش {begin} تا {end} از {totalCount} پذیرش',
    'emptyText' => 'پذیرشی ثبت نشده است.',
    'columns' => [
        'id',
        ['label' => 'بیمار', 'value' => static function ($model) {
            return $model->patient->first_name . ' ' . $model->patient->last_name;
        }],
        'admission_date', 'doctor_name', 'ward',
        ['attribute' => 'status', 'value' => static function ($model) {
            return $model->status === 'admitted' ? 'بستری' : 'ترخیص‌شده';
        }],
        ['label' => 'عملیات', 'format' => 'raw', 'value' => static function ($model) {
            return Html::a('خلاصه پرونده', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']);
        }],
    ],
]) ?>
</div>
