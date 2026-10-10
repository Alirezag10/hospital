<?php
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = 'بیماران';
$this->params['breadcrumbs'][] = $this->title;
$hasFilters = (string) $searchModel->name !== '' || (string) $searchModel->national_code !== '' || (string) $searchModel->mobile !== '';
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p>بیمار را پیدا کنید و پذیرش جدید ثبت کنید.</p></div>
    <?= Html::a('ثبت بیمار جدید', ['create'], ['class' => 'btn btn-primary']) ?>
</div>
<?= $this->render('_search', ['model' => $searchModel]) ?>
<?= $this->render('../layouts/_page_size', ['dataProvider' => $dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin} تا {end} از {totalCount} بیمار',
    'emptyText' => Html::tag('div',
        Html::tag('p', $hasFilters ? 'بیماری مطابق جست‌وجوی شما پیدا نشد.' : 'هنوز بیماری ثبت نشده است.') .
        Html::a($hasFilters ? 'پاک کردن جست‌وجو' : 'ثبت اولین بیمار',
            $hasFilters ? ['index'] : ['create'], ['class' => 'btn btn-primary']),
        ['class' => 'empty-state']
    ),
    'columns' => [
        ['class' => 'yii\grid\SerialColumn', 'header' => 'ردیف'],
        'first_name', 'last_name', 'national_code', 'mobile',
        ['label' => 'عملیات', 'format' => 'raw', 'value' => static function ($model) {
            return Html::a('پرونده', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) . ' '
                . Html::a('ثبت پذیرش', ['/admission/create', 'patient_id' => $model->id], ['class' => 'btn btn-sm btn-primary']);
        }],
    ],
]) ?>
</div>
