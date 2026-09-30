<?php
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
/** @var yii\web\View $this */
/** @var app\models\ServiceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'خدمات';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-index" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('تعریف خدمت جدید', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>
    <p class="text-muted">برای جست‌وجو از فیلترهای بالای ستون‌ها استفاده کنید.</p>
    <div class="mb-3"><?= Html::a('پاک کردن فیلترها', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?></div>
    <div class="card"><div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'summary' => 'نمایش {begin} تا {end} از {totalCount} رکورد',
            'emptyText' => 'رکوردی مطابق جست‌وجو پیدا نشد.',
            'layout' => '{summary}<div class="table-responsive mt-3">{items}</div>{pager}',
            'tableOptions' => ['class' => 'table table-striped table-hover align-middle'],
            'pager' => ['prevPageLabel' => 'قبلی', 'nextPageLabel' => 'بعدی'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn', 'header' => 'ردیف'],
                'id', 'title',
  ['attribute' => 'price', 'label' => 'قیمت (تومان)', 'format' => ['decimal', 0]],
  ['attribute' => 'active', 'label' => 'وضعیت', 'filter' => [1 => 'فعال', 0 => 'غیرفعال'], 'value' => static function ($model) { return (int) $model->active === 1 ? 'فعال' : 'غیرفعال'; }],
                [
    'class' => ActionColumn::class,
    'header' => 'عملیات',
    'template' => '{view} {update} {delete}',
    'contentOptions' => ['class' => 'text-nowrap'],

    'buttons' => [
        'view' => static function ($url, $model) {
            return Html::a('مشاهده', $url, ['class' => 'btn btn-sm btn-outline-primary']);
        },
        'update' => static function ($url, $model) {
            return Html::a('ویرایش', $url, ['class' => 'btn btn-sm btn-outline-secondary']);
        },
        'delete' => static function ($url, $model) {
            return Html::a('حذف', $url, [
                'class' => 'btn btn-sm btn-outline-danger',
                'data' => ['method' => 'post', 'confirm' => 'این رکورد حذف شود؟'],
            ]);
        },
    ],
    'urlCreator' => static function ($action, $model, $key, $index, $column) {
        return Url::toRoute([$action, 'id' => $model->id]);
    },
],
            ],
        ]) ?>
    </div></div>
</div>
