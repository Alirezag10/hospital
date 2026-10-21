<?php
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\PrescriptionItem;
$this->title='دارو و لوازم مصرفی';
$this->params['breadcrumbs'][]=$this->title;
?>
<div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>اقلام قابل انتخاب در نسخه و تعرفهٔ فعلی آن‌ها را مدیریت کنید.</p></div><?= Html::a('افزودن قلم',['create'],['class'=>'btn btn-primary']) ?></div>
<div class="card mb-3"><div class="card-body">
<?= Html::beginForm(['index'],'get',['role'=>'search']) ?><div class="row g-3 align-items-end">
<div class="col-md-6"><?= Html::label('نام قلم','catalog-query',['class'=>'form-label']) ?><?= Html::textInput('q',$q,['id'=>'catalog-query','class'=>'form-control']) ?></div>
<div class="col-md-3"><?= Html::label('نوع','catalog-kind',['class'=>'form-label']) ?><?= Html::dropDownList('kind',$kind,PrescriptionItem::kindLabels(),['id'=>'catalog-kind','class'=>'form-select','prompt'=>'همه اقلام']) ?></div>
<div class="col-md-3 form-actions"><?= Html::submitButton('جست‌وجو',['class'=>'btn btn-primary']) ?><?= Html::a('پاک کردن',['index'],['class'=>'btn btn-outline-secondary']) ?></div>
</div><?= Html::endForm() ?></div></div>
<?= $this->render('/layouts/_page_size',['dataProvider'=>$dataProvider]) ?>
<div class="table-panel table-responsive"><?= GridView::widget(['dataProvider'=>$dataProvider,'tableOptions'=>['class'=>'table table-hover align-middle'],'summary'=>'نمایش {begin, number, integer} تا {end, number, integer} از {totalCount, number, integer} قلم','emptyText'=>'قلمی پیدا نشد.','columns'=>[
    'title',['attribute'=>'kind','value'=>static fn($m)=>PrescriptionItem::kindLabels()[$m->kind]],
    ['attribute'=>'unit_price','format'=>'integer'],['attribute'=>'active','value'=>static fn($m)=>$m->active?'فعال':'غیرفعال'],
    ['label'=>'عملیات','format'=>'raw','value'=>static fn($m)=>Html::a('ویرایش',['update','id'=>$m->id],['class'=>'btn btn-sm btn-outline-primary'])],
]]) ?></div>
