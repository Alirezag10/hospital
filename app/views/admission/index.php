<?php
use yii\grid\GridView;
use yii\helpers\Html;
use app\components\Access;
use app\models\User;
$this->title = $dischargedOn !== null ? 'ترخیص‌های روز ' . Yii::$app->formatter->asDate($dischargedOn) : (Access::is(User::DOCTOR) ? 'بیماران من' : 'پذیرش‌ها');
$this->params['breadcrumbs'][] = $this->title;
$hasFilters = $dischargedOn !== null || $date !== null || (string) $searchModel->name !== '' || (string) $searchModel->national_code !== ''
    || (string) $searchModel->mobile !== '' || (string) $searchModel->status !== '';
$onlyDischargeDate = $dischargedOn !== null && $date === null && (string) $searchModel->name === ''
    && (string) $searchModel->national_code === '' && (string) $searchModel->mobile === ''
    && in_array((string) $searchModel->status, ['', 'discharged'], true);
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p><?= $dischargedOn !== null ? 'پرونده‌های ترخیص‌شده در این روز را بررسی کنید.' : ($date === 'unknown' ? 'پذیرش‌های قدیمی با تاریخ نامشخص' : ($date !== null ? 'پذیرش‌های روز ' . Html::encode(Yii::$app->formatter->asDate($date)) : 'وضعیت پذیرش‌ها و خلاصهٔ پرونده‌ها را بررسی کنید.')) ?></p></div>
    <?php if (Access::allows('/admission/create')): ?><div class="form-actions mt-0"><?= Html::a('تقویم پذیرش‌ها', ['calendar'], ['class' => 'btn btn-outline-primary']) ?><?= Html::a('ثبت پذیرش', ['create'], ['class' => 'btn btn-primary']) ?></div><?php endif; ?>
</div>
<?= $this->render('_search', ['model' => $searchModel, 'date' => $date, 'dischargedOn' => $dischargedOn]) ?>
<?= $this->render('../layouts/_page_size', ['dataProvider' => $dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin, number, integer} تا {end, number, integer} از {totalCount, number, integer} پذیرش',
    'emptyText' => $this->render('../layouts/_empty_state', [
        'icon' => 'admission',
        'message' => $onlyDischargeDate ? 'در این روز ترخیصی ثبت نشده است.' : ($hasFilters ? 'پذیرشی مطابق جست‌وجوی شما پیدا نشد. فیلترها را بررسی کنید.' : 'هنوز پذیرشی ثبت نشده است.'),
        'action' => $hasFilters ? 'پاک کردن فیلترها' : (Access::allows('/admission/create') ? 'ثبت اولین پذیرش' : null),
        'route' => $hasFilters ? ['index'] : (Access::allows('/admission/create') ? ['create'] : null),
    ]),
    'columns' => [
        ['label' => 'بیمار', 'format' => 'raw', 'value' => function ($model) {
            return $this->render('/layouts/_patient_label', ['patient' => $model->patient]);
        }],
        $dischargedOn !== null
            ? ['label' => 'تاریخ ترخیص', 'value' => static fn($model) => $model->discharge?->discharge_date, 'format' => 'datetime', 'contentOptions' => ['dir' => 'ltr']]
            : ['attribute' => 'admission_date', 'format' => 'datetime', 'contentOptions' => ['dir' => 'ltr']],
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
