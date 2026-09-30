<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
/** @var yii\web\View $this */
/** @var app\models\Discharge $model */
$admission = $model->admission;
$patient = $admission?->patient;
$patientName = $patient ? $patient->first_name . ' ' . $patient->last_name : 'بیمار نامشخص';
$canEdit = false;
$this->title = 'ترخیص #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'ترخیص‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="discharge-view" dir="rtl">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <?php if ($admission !== null): ?>
    <?= Html::a('خلاصه پرونده و هزینه‌ها', ['/admission/summary', 'id' => $admission->id], ['class' => 'btn btn-primary']) ?>
<?php endif; ?>

        <?= Html::a('بازگشت به فهرست', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <div class="alert alert-info">ترخیص ثبت‌شده قابل ویرایش یا حذف نیست.</div>
    <div class="card"><div class="card-body table-responsive">
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-striped table-bordered detail-view mb-0'],
            'attributes' => ['id', 'admission_id',
  ['label' => 'بیمار', 'value' => $patientName],
   ['attribute' => 'discharge_date', 'label' => 'تاریخ ترخیص (شمسی)'],
   ['attribute' => 'total_amount', 'label' => 'مبلغ نهایی (تومان)', 'format' => ['decimal', 0]],
   ['attribute' => 'description', 'format' => 'ntext', 'value' => $model->description ?: 'ندارد'],
   ['attribute' => 'created_at', 'label' => 'تاریخ ثبت (شمسی)'],],
        ]) ?>
    </div></div>
</div>
