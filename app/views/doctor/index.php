<?php

use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $term */
$this->title = 'پزشکان';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p>پزشکان قابل انتخاب در پذیرش را مدیریت کنید.</p></div>
    <?= Html::a('ثبت پزشک جدید', ['create'], ['class' => 'btn btn-primary']) ?>
</div>
<div class="card mb-3"><div class="card-body">
    <?= Html::beginForm(['index'], 'get', ['role' => 'search', 'aria-label' => 'جست‌وجوی پزشکان']) ?>
    <label class="form-label" for="doctor-search">جست‌وجو در پزشکان</label>
    <div class="form-actions mt-0">
        <?= Html::textInput('q', $term, ['id' => 'doctor-search', 'class' => 'form-control search-control', 'placeholder' => 'نام، تخصص یا موبایل']) ?>
        <?= Html::submitButton('جست‌وجو', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('پاک کردن', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?= Html::endForm() ?>
</div></div>
<?= $this->render('../layouts/_page_size', ['dataProvider' => $dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin} تا {end} از {totalCount} پزشک',
    'emptyText' => Html::tag('div',
        Html::tag('p', $term !== '' ? 'پزشکی با این مشخصات پیدا نشد.' : 'هنوز پزشکی ثبت نشده است.') .
        Html::a($term !== '' ? 'پاک کردن جست‌وجو' : 'ثبت اولین پزشک', $term !== '' ? ['index'] : ['create'], ['class' => 'btn btn-primary']),
        ['class' => 'empty-state']
    ),
    'columns' => [
        ['class' => 'yii\grid\SerialColumn', 'header' => 'ردیف'],
        'name',
        'specialty',
        ['attribute' => 'mobile', 'contentOptions' => ['dir' => 'ltr']],
        ['attribute' => 'created_at', 'contentOptions' => ['dir' => 'ltr']],
    ],
]) ?>
</div>
