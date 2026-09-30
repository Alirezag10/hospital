<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
/** @var yii\web\View $this */
/** @var app\models\AdmissionService $model */
$admission = $model->admission;
$patient = $admission?->patient;
$patientName = $patient ? $patient->first_name . ' ' . $patient->last_name : 'بیمار نامشخص';
$canEdit = $admission !== null && $admission->status === 'admitted' && $admission->discharge === null;
$this->title = 'خدمت پذیرش #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'خدمات پذیرش', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="admission-service-view" dir="rtl">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <?php if ($admission !== null): ?>
    <?= Html::a('خلاصه پرونده و هزینه‌ها', ['/admission/summary', 'id' => $admission->id], ['class' => 'btn btn-primary']) ?>
<?php endif; ?>
        <?php if ($canEdit): ?>
        <?= Html::a('ویرایش', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a('حذف', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-outline-danger',
            'data' => ['confirm' => 'این رکورد حذف شود؟', 'method' => 'post'],
        ]) ?>
    <?php endif; ?>
        <?= Html::a('بازگشت به فهرست', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?php if (!$canEdit): ?><div class="alert alert-info">خدمات این پذیرش قابل ویرایش یا حذف نیست.</div><?php endif; ?>
    <div class="card"><div class="card-body table-responsive">
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-striped table-bordered detail-view mb-0'],
            'attributes' => ['id', 'admission_id',
  ['label' => 'بیمار', 'value' => $patientName],
   ['attribute' => 'service_id', 'label' => 'خدمت', 'value' => $model->service?->title ?? 'خدمت نامشخص'],
   'quantity',
   ['attribute' => 'unit_price', 'label' => 'قیمت واحد ثبت‌شده (تومان)', 'format' => ['decimal', 0]],
   ['label' => 'مبلغ (تومان)', 'value' => (int) $model->quantity * (int) $model->unit_price, 'format' => ['decimal', 0]],
   ['attribute' => 'created_at', 'label' => 'تاریخ ثبت (شمسی)'],],
        ]) ?>
    </div></div>
</div>
