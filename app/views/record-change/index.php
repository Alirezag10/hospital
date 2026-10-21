<?php
use yii\helpers\Html;
$this->title='سابقه اصلاحات '.($type==='prescription'?'نسخه':'خدمت');
$this->params['breadcrumbs'][]=['label'=>'پرونده بیمار','url'=>['/admission/view','id'=>$admission->id]];$this->params['breadcrumbs'][]=$this->title;
?>
<div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>مقدار پیش و پس از هر اصلاح، همراه با دلیل و ثبت‌کننده نگهداری می‌شود.</p></div><?= Html::a('بازگشت به پرونده',['/admission/view','id'=>$admission->id],['class'=>'btn btn-outline-secondary']) ?></div>
<?php if(!$changes): ?><div class="card"><div class="card-body">هنوز اصلاحی برای این رکورد ثبت نشده است.</div></div><?php endif; ?>
<?php foreach($changes as $change): ?><article class="card mb-3"><div class="card-body">
<h2 class="h5"><?= Html::encode($change->actor->full_name) ?> · <?= Html::encode(Yii::$app->formatter->asDatetime($change->created_at)) ?></h2><p>دلیل: <?= Html::encode($change->reason) ?></p>
<div class="change-comparison"><section><h3 class="h6">پیش از اصلاح</h3><?= $this->render('_snapshot',['data'=>json_decode($change->before_data,true)??[],'type'=>$type,'serviceNames'=>$serviceNames]) ?></section><section><h3 class="h6">پس از اصلاح</h3><?= $this->render('_snapshot',['data'=>json_decode($change->after_data,true)??[],'type'=>$type,'serviceNames'=>$serviceNames]) ?></section></div>
</div></article><?php endforeach; ?>
