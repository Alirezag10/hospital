<?php

use app\models\Doctor;
use app\models\Patient;
use app\models\Ward;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Admission $model */

$isClosed = !$model->isNewRecord
    && ($model->status !== 'admitted' || $model->discharge !== null);

$patients = ArrayHelper::map(
    Patient::find()->orderBy(['last_name' => SORT_ASC, 'first_name' => SORT_ASC])->all(),
    'id',
    static function (Patient $patient): string {
        return $patient->first_name . ' ' . $patient->last_name
            . ' — کد ملی: ' . $patient->national_code;
    }
);
$doctors = ArrayHelper::map(Doctor::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
$wards = ArrayHelper::map(Ward::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
?>
<div class="admission-form card" dir="rtl">
    <div class="card-body">
        <?php if ($isClosed): ?>
            <div class="alert alert-info">این پذیرش قابل ویرایش نیست. اطلاعات آن را در پرونده مشاهده کنید.</div>
            <?= Html::a('مشاهده پذیرش', ['view', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php else: ?>
            <p class="text-muted mb-3">بیمار، پزشک و بخش را انتخاب کنید. ترخیص از صفحهٔ مخصوص ترخیص انجام می‌شود.</p>
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->errorSummary($model) ?>
            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'patient_id')->dropDownList($patients, ['prompt' => 'انتخاب بیمار']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'doctor_id')->label('پزشک')->dropDownList($doctors, ['prompt' => 'انتخاب پزشک']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'ward_id')->label('بخش')->dropDownList($wards, ['prompt' => 'انتخاب بخش']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'admission_date')->textInput([
                        'dir' => 'ltr',
                        'placeholder' => '۱۴۰۵/۰۷/۰۸ ۱۴:۳۰:۰۰',
                    ])->hint('تاریخ و ساعت شمسی؛ از تقویم انتخاب کنید. در ثبت جدید، خالی بگذارید تا زمان فعلی ثبت شود.') ?>
                </div>
                <div class="col-md-6">
                    <p class="mb-2">وضعیت پذیرش</p>
                    <span class="badge bg-success">بستری</span>
                    <p class="text-muted small mt-2">وضعیت با ثبت ترخیص به‌صورت خودکار تغییر می‌کند.</p>
                </div>
            </div>
            <?php if (!$model->isNewRecord): ?>
                <p class="text-muted">تاریخ ثبت: <span dir="ltr"><?= Html::encode($model->created_at) ?></span></p>
            <?php endif; ?>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <?= Html::submitButton($model->isNewRecord ? 'ثبت پذیرش' : 'ذخیره تغییرات', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('انصراف', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        <?php endif; ?>
    </div>
</div>
