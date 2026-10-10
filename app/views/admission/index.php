<?php
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = 'پذیرش‌ها';
$this->params['breadcrumbs'][] = $this->title;
$hasFilters = (string) $searchModel->name !== '' || (string) $searchModel->national_code !== ''
    || (string) $searchModel->status !== '';
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p>وضعیت پذیرش‌ها و خلاصهٔ پرونده‌ها را بررسی کنید.</p></div>
    <div class="form-actions mt-0"><?= Html::a('تقویم پذیرش‌ها', ['calendar'], ['class' => 'btn btn-outline-primary']) ?><?= Html::a('ثبت پذیرش', ['create'], ['class' => 'btn btn-primary']) ?></div>
</div>
<?= $this->render('_search', ['model' => $searchModel]) ?>
<?= $this->render('../layouts/_page_size', ['dataProvider' => $dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin} تا {end} از {totalCount} پذیرش',
    'emptyText' => Html::tag('div',
        Html::tag('p', $hasFilters ? 'پذیرشی مطابق جست‌وجوی شما پیدا نشد.' : 'هنوز پذیرشی ثبت نشده است.') .
        Html::a($hasFilters ? 'پاک کردن فیلترها' : 'ثبت اولین پذیرش',
            $hasFilters ? ['index'] : ['create'], ['class' => 'btn btn-primary']),
        ['class' => 'empty-state']
    ),
    'columns' => [
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
