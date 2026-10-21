<?php
use app\models\PrescriptionItem;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = $editing?'اصلاح نسخه و اقلام درمانی':'ثبت نسخه و اقلام درمانی';
$this->params['breadcrumbs'][] = ['label' => 'بیماران من', 'url' => ['/admission/index']];
$this->params['breadcrumbs'][] = ['label' => 'پرونده بیمار', 'url' => ['/admission/view', 'id' => $admission->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="clinical-create">
    <div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>تشخیص، دستورات و اقلام موردنیاز این پذیرش را ثبت کنید.</p></div>
        <?= Html::a('بازگشت به پرونده', ['/admission/view', 'id' => $admission->id], ['class' => 'btn btn-outline-secondary']) ?></div>
    <section class="clinical-patient-strip" aria-label="بیمار انتخاب‌شده">
        <?= $this->render('/layouts/_patient_label', ['patient' => $admission->patient]) ?>
        <span>بخش <?= Html::encode($admission->ward) ?></span><span>پزشک: <?= Html::encode($admission->doctor_name) ?></span>
    </section>
    <div class="card"><div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'prescription-form', 'enableClientValidation' => false,
            'options' => ['data-loading-form' => '1', 'data-unsaved-warning' => '1', 'data-prescription-form' => '1']]); ?>
        <?= $form->errorSummary(array_merge([$model,$correction], $items)) ?>
        <fieldset class="form-section"><legend>شرح و دستورات پزشک</legend>
            <?= $form->field($model, 'diagnosis')->textarea(['rows' => 3, 'maxlength' => 2000, 'placeholder' => 'علائم، تشخیص و شرح وضعیت بیمار']) ?>
            <?= $form->field($model, 'notes')->textarea(['rows' => 2, 'maxlength' => 5000, 'placeholder' => 'توصیه‌های مراقبتی و دستورات تکمیلی'])->label('توصیه‌ها و دستورات پزشک (اختیاری)') ?>
        </fieldset>
        <section aria-labelledby="prescription-items-title">
            <div class="clinical-section-heading"><div><h2 id="prescription-items-title">دارو و لوازم مصرفی</h2><p>دارو یا وسیله را از فهرست جست‌وجو و انتخاب کنید. قیمت واحد به تومان است.</p></div>
                <button type="button" class="btn btn-outline-primary" data-add-prescription-item>افزودن قلم</button></div>
            <div data-prescription-items>
                <?php foreach ($items as $index => $item): $original=$originals[$item->sourceItemId??$item->id]??null; ?><?= $this->render('_item_row', compact('index', 'item','original')) ?><?php endforeach; ?>
            </div>
            <p class="clinical-estimate">جمع اقلام این نسخه: <strong data-prescription-total aria-live="polite">۰ تومان</strong></p>
        </section>
        <?php if($editing): ?><?= $form->field($correction,'reason')->textarea(['rows'=>2,'maxlength'=>500]) ?><?= Html::activeHiddenInput($correction,'revision') ?><?php endif; ?>
        <div class="form-actions">
            <?= Html::submitButton($editing?'ذخیره اصلاح نسخه':'ثبت نسخه و اقلام', ['class' => 'btn btn-primary', 'data-loading-label' => 'در حال ثبت نسخه…']) ?>
            <?= Html::a('انصراف', ['/admission/view', 'id' => $admission->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div></div>
    <template id="prescription-item-template"><?= $this->render('_item_row', ['index' => '__index__',
        'item' => new PrescriptionItem(['kind' => 'medicine', 'quantity' => 1, 'unit_price' => 0])]) ?></template>
</div>
