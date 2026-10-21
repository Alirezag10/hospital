<?php
use yii\helpers\Html;
use yii\grid\GridView;
$this->title='خدمات و تعرفه‌ها';
$this->params['breadcrumbs'][]=$this->title;
?>
<div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>خدمات قابل انتخاب هنگام درمان و تعرفهٔ ثبت‌های جدید را مدیریت کنید.</p></div>
<?= Html::a('تعریف خدمت',['create'],['class'=>'btn btn-primary']) ?></div>
<div class="card mb-3 search-panel"><div class="card-body">
<?= Html::beginForm(['index'],'get',['role'=>'search','aria-label'=>'جست‌وجوی خدمات']) ?>
<div class="row g-3"><div class="col-md-8"><?= Html::label('عنوان خدمت','service-search',['class'=>'form-label']) ?>
<?= Html::textInput('q',$term,['id'=>'service-search','class'=>'form-control','maxlength'=>150]) ?></div>
<div class="col-md-4"><?= Html::label('وضعیت','service-status',['class'=>'form-label']) ?>
<?= Html::dropDownList('active',$status,[''=>'همه','1'=>'فعال','0'=>'غیرفعال'],['id'=>'service-status','class'=>'form-select']) ?></div></div>
<div class="form-actions"><?= Html::submitButton('جست‌وجو',['class'=>'btn btn-primary']) ?><?= Html::a('پاک کردن',['index'],['class'=>'btn btn-outline-secondary']) ?></div>
<?= Html::endForm() ?></div></div>
<?= $this->render('/layouts/_page_size',['dataProvider'=>$dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget(['dataProvider'=>$dataProvider,'tableOptions'=>['class'=>'table table-hover align-middle mb-0'],
    'summary'=>'نمایش {begin, number, integer} تا {end, number, integer} از {totalCount, number, integer} خدمت',
    'emptyText'=>'خدمتی مطابق جست‌وجو پیدا نشد.','columns'=>[
        'title',['attribute'=>'price','label'=>'تعرفه (تومان)','format'=>'integer'],
        ['attribute'=>'active','format'=>'raw','value'=>static fn($model)=>Html::tag('span',$model->active?'فعال':'غیرفعال',['class'=>'status-badge '.($model->active?'status-admitted':'status-discharged')])],
        ['label'=>'عملیات','format'=>'raw','value'=>static fn($model)=>Html::a('ویرایش',['update','id'=>$model->id],['class'=>'btn btn-sm btn-outline-primary'])],
    ]]) ?></div>
