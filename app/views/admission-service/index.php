<?php
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Admission;
use yii\helpers\ArrayHelper;
use app\models\Service;
/** @var yii\web\View $this */
/** @var app\models\AdmissionServiceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'خدمات پذیرش';
$this->params['breadcrumbs'][] = $this->title;
$admissions = ArrayHelper::map(Admission::find()->with('patient')->orderBy(['id' => SORT_DESC])->all(), 'id', static function ($admission) {
    $patient = $admission->patient;
    return '#' . $admission->id . ' — ' . ($patient ? $patient->first_name . ' ' . $patient->last_name : 'بیمار نامشخص');
});
$dataProvider->query->with(['admission.patient', 'admission.discharge', 'service']);
$services = ArrayHelper::map(Service::find()->orderBy(['title' => SORT_ASC])->all(), 'id', 'title');
?>
<div class="admission-service-index" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('ثبت خدمت برای پذیرش', ['create'], ['class' => 'btn btn-primary']) ?>
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
                'id',
  ['attribute' => 'admission_id', 'label' => 'پذیرش بیمار', 'filter' => $admissions, 'value' => static function ($model) {
      $admission = $model->admission;
      if ($admission === null) { return 'پذیرش نامشخص'; }
      $patient = $admission->patient;
      return '#' . $admission->id . ' — ' . ($patient ? $patient->first_name . ' ' . $patient->last_name : 'بیمار نامشخص');
  }],
   ['attribute' => 'service_id', 'label' => 'خدمت', 'filter' => $services, 'value' => static function ($model) { return $model->service?->title ?? 'خدمت نامشخص'; }],
   'quantity',
   ['attribute' => 'unit_price', 'label' => 'قیمت واحد (تومان)', 'format' => ['decimal', 0]],
   ['label' => 'مبلغ (تومان)', 'format' => ['decimal', 0], 'value' => static function ($model) { return (int) $model->quantity * (int) $model->unit_price; }],
                [
    'class' => ActionColumn::class,
    'header' => 'عملیات',
    'template' => '{view} {update} {delete}',
    'contentOptions' => ['class' => 'text-nowrap'],
    'visibleButtons' => [
      'update' => static function ($model) { return $model->admission !== null && $model->admission->status === 'admitted' && $model->admission->discharge === null; },
      'delete' => static function ($model) { return $model->admission !== null && $model->admission->status === 'admitted' && $model->admission->discharge === null; },
  ],
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
