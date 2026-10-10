<?php
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'پرونده بیمار: ' . $model->first_name . ' ' . $model->last_name;
$this->params['breadcrumbs'][] = ['label' => 'بیماران', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p>مشخصات و سابقه پذیرش‌های بیمار.</p></div>
    <div class="form-actions mt-0"><?= Html::button('چاپ پرونده بیمار', ['class' => 'btn btn-outline-secondary', 'data-print-trigger' => '1']) ?><?= Html::a('ثبت پذیرش جدید', ['/admission/create', 'patient_id' => $model->id], ['class' => 'btn btn-primary']) ?></div>
</div>

<section class="patient-profile" aria-labelledby="patient-info-title">
    <h2 id="patient-info-title">مشخصات بیمار</h2>
    <dl class="detail-list">
        <dt>نام و نام خانوادگی</dt><dd><?= Html::encode($model->first_name . ' ' . $model->last_name) ?></dd>
        <dt>کد ملی</dt><dd dir="ltr"><?= Html::encode($model->national_code) ?></dd>
        <dt>موبایل</dt><dd dir="ltr"><?= Html::encode($model->mobile) ?></dd>
        <?php if ($model->birth_date): ?><dt>تاریخ تولد</dt><dd dir="ltr"><?= Html::encode($model->birth_date) ?></dd><?php endif; ?>
    </dl>
</section>

<h2 class="section-title">سابقه پذیرش‌ها</h2>
<?= $this->render('../layouts/_page_size', ['dataProvider' => $admissions]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $admissions,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin} تا {end} از {totalCount} پذیرش',
    'emptyText' => Html::tag('div', 'برای این بیمار هنوز پذیرشی ثبت نشده است.', ['class' => 'empty-state']),
    'columns' => [
        ['class' => 'yii\\grid\\SerialColumn', 'header' => 'ردیف'],
        ['attribute' => 'admission_date', 'label' => 'تاریخ پذیرش', 'contentOptions' => ['dir' => 'ltr']],
        'doctor_name', 'ward',
        ['label' => 'وضعیت', 'format' => 'raw', 'value' => static function ($admission) {
            $open = $admission->status === 'admitted' && $admission->discharge === null;
            return Html::tag('span', $open ? 'بستری' : 'ترخیص‌شده', ['class' => 'status-badge ' . ($open ? 'status-admitted' : 'status-discharged')]);
        }],
        ['label' => 'عملیات', 'format' => 'raw', 'value' => static function ($admission) {
            return Html::a('مشاهده پرونده', ['/admission/view', 'id' => $admission->id], ['class' => 'btn btn-sm btn-outline-primary']);
        }],
    ],
]) ?>
</div>
<div class="form-actions"><?= Html::a('بازگشت به بیماران', ['index'], ['class' => 'btn btn-outline-secondary']) ?></div>
