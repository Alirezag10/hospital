<?php
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = $model->user === null ? 'ساخت کاربر جدید' : 'ویرایش حساب کاربر';
$this->params['breadcrumbs'][] = ['label' => 'کاربران', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$options = $names = [];
foreach ($doctors as $doctor) {
    $options[$doctor->id] = $doctor->name . ' (' . $doctor->specialty . ')';
    $names[$doctor->id] = ['data-name' => $doctor->name];
}
?>
<div class="form-page user-form-page">
    <div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>دسترسی کاربر بر اساس نقشی که اینجا تعیین می‌کنید اعمال می‌شود.</p></div></div>
    <div class="card"><div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'user-form', 'enableClientValidation' => false, 'options' => ['data-user-form' => '1', 'data-loading-form' => '1', 'data-unsaved-warning' => '1']]); ?>
        <?= $form->errorSummary($model) ?>
        <fieldset class="form-section"><legend>هویت و دسترسی</legend>
            <div class="row g-3">
                <div class="col-md-6"><?= $form->field($model, 'full_name')->textInput(['maxlength' => 100, 'autocomplete' => 'off', 'data-user-full-name' => '1']) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'username')->textInput(['dir' => 'ltr', 'maxlength' => 50, 'autocomplete' => 'off']) ?></div>
                <div class="col-md-6">
                    <?php if ($doctorLocked): ?>
                        <?= Html::activeHiddenInput($model, 'role', ['data-user-role' => '1']) ?>
                        <?= Html::label('نقش', 'locked-user-role', ['class' => 'form-label']) ?><?= Html::textInput('role_display', 'پزشک', ['id' => 'locked-user-role', 'class' => 'form-control', 'readonly' => true]) ?>
                    <?php else: ?><?= $form->field($model, 'role')->dropDownList(User::roleLabels(), ['data-user-role' => '1']) ?><?php endif; ?>
                </div>
                <div class="col-md-6"><?= $form->field($model, 'is_active')->dropDownList([1 => 'فعال', 0 => 'غیرفعال']) ?></div>
            </div>
        </fieldset>
        <fieldset class="form-section" data-user-doctor <?= $model->role === User::DOCTOR ? '' : 'hidden' ?>>
            <legend>پروفایل پزشک</legend>
            <?php if ($doctorLocked): ?>
                <?= Html::activeHiddenInput($model, 'doctor_id', ['data-user-doctor-id' => '1']) ?>
                <p><?= Html::encode($options[$model->doctor_id] ?? $model->full_name) ?></p>
            <?php else: ?>
                <?= $form->field($model, 'doctor_id')->dropDownList($options, ['prompt' => 'ساخت پروفایل پزشک جدید', 'options' => $names, 'data-user-doctor-id' => '1']) ?>
            <?php endif; ?>
            <div class="row g-3" data-new-doctor-fields>
                <div class="col-md-6"><?= $form->field($model, 'specialty')->textInput(['maxlength' => 100]) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'mobile')->textInput(['dir' => 'ltr', 'inputmode' => 'tel', 'maxlength' => 20]) ?></div>
            </div>
        </fieldset>
        <fieldset class="form-section"><legend><?= $model->user === null ? 'رمز ورود' : 'تغییر رمز ورود (اختیاری)' ?></legend>
            <?php if ($model->user !== null): ?><p class="form-section-note">برای حفظ رمز فعلی، هر دو فیلد را خالی بگذارید.</p><?php endif; ?>
            <div class="row g-3">
                <?php foreach (['password', 'password_repeat'] as $attribute): ?>
                    <?php $toggle = $this->render('/layouts/_password_button', ['inputId' => Html::getInputId($model, $attribute)]); ?>
                    <div class="col-md-6"><?= $form->field($model, $attribute, ['template' => "{label}\n<div class=\"password-control\">{input}$toggle</div>\n{error}"])->passwordInput(['dir' => 'ltr', 'autocomplete' => 'new-password', 'maxlength' => 72]) ?></div>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <div class="form-actions"><?= Html::submitButton($model->user === null ? 'ساخت حساب' : 'ذخیره تغییرات', ['class' => 'btn btn-primary', 'data-loading-label' => 'در حال ذخیره…']) ?><?= Html::a('انصراف', ['index'], ['class' => 'btn btn-outline-secondary']) ?></div>
        <?php ActiveForm::end(); ?>
    </div></div>
</div>
