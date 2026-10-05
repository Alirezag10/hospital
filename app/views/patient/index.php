<?php
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = 'بیماران';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>
<p><?= Html::a('ثبت بیمار جدید', ['create'], ['class' => 'btn btn-primary']) ?></p>
<?= $this->render('_search', ['model' => $searchModel]) ?>
<div class="table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => 'نمایش {begin} تا {end} از {totalCount} بیمار',
    'emptyText' => 'بیماری مطابق جست‌وجوی شما پیدا نشد.',
    'columns' => [
        ['class' => 'yii\grid\SerialColumn', 'header' => 'ردیف'],
        'first_name', 'last_name', 'national_code', 'mobile',
        ['label' => 'عملیات', 'format' => 'raw', 'value' => static function ($model) {
            return Html::a('ثبت پذیرش', ['/admission/create', 'patient_id' => $model->id], ['class' => 'btn btn-sm btn-primary']);
        }],
    ],
]) ?>
</div>
