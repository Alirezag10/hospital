<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */

$patient = $model->patient;
$patientName = $patient ? $patient->first_name . ' ' . $patient->last_name : 'بیمار نامشخص';
$isOpen = $model->status === 'admitted' && $model->discharge === null;
$statusLabels = ['admitted' => 'بستری', 'discharged' => 'ترخیص‌شده'];
$this->title = 'پذیرش #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'پذیرش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="admission-view" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-2"><?= Html::encode($this->title) ?></h1>
            <p class="text-muted mb-0"><?= Html::encode($patientName) ?></p>
        </div>
        <?= Html::tag('span', Html::encode($statusLabels[$model->status] ?? $model->status), [
            'class' => 'badge ' . ($isOpen ? 'bg-success' : 'bg-secondary'),
        ]) ?>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-4">
        <?= Html::a('خلاصه پرونده و هزینه‌ها', ['summary', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('بازگشت به پذیرش‌ها', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?php if ($isOpen): ?>
            <?= Html::a('ویرایش پذیرش', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('حذف پذیرش', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-outline-danger',
                'data' => ['confirm' => 'این پذیرش حذف شود؟', 'method' => 'post'],
            ]) ?>
        <?php endif; ?>
    </div>
    <?php if (!$isOpen): ?>
        <div class="alert alert-info">ویرایش و حذف این پذیرش غیرفعال است؛ خلاصه پرونده قابل مشاهده است.</div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body table-responsive">
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-striped table-bordered detail-view mb-0'],
                'attributes' => [
                    'id',
                    ['attribute' => 'patient_id', 'label' => 'بیمار', 'value' => $patientName],
                    ['label' => 'کد ملی بیمار', 'value' => $patient?->national_code ?? '-'],
                    ['attribute' => 'admission_date', 'label' => 'تاریخ پذیرش (شمسی)'],
                    ['attribute' => 'doctor_id', 'label' => 'پزشک', 'value' => $model->doctor?->name ?? '-'],
                    ['attribute' => 'ward_id', 'label' => 'بخش', 'value' => $model->wardModel?->name ?? '-'],
                    ['attribute' => 'status', 'value' => $statusLabels[$model->status] ?? $model->status],
                    ['attribute' => 'created_at', 'label' => 'تاریخ ثبت (شمسی)'],
                ],
            ]) ?>
        </div>
    </div>
</div>
