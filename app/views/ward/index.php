<?php

use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $term */
$this->title = 'بخش‌ها';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p>بخش‌های قابل انتخاب در پذیرش را مدیریت کنید.</p></div>
    <?= Html::a('ثبت بخش جدید', ['create'], ['class' => 'btn btn-primary']) ?>
</div>
<div class="card mb-3"><div class="card-body">
    <?= Html::beginForm(['index'], 'get', ['role' => 'search', 'aria-label' => 'جست‌وجوی بخش‌ها']) ?>
    <label class="form-label" for="ward-search">جست‌وجو در بخش‌ها</label>
    <div class="form-actions mt-0">
        <?= Html::textInput('q', $term, ['id' => 'ward-search', 'class' => 'form-control search-control', 'placeholder' => 'نام بخش یا طبقه']) ?>
        <?= Html::submitButton('جست‌وجو', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('پاک کردن', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?= Html::endForm() ?>
    <?= $this->render('/layouts/_filters', ['formName' => '', 'filters' => ['q' => ['جست‌وجو', $term]]]) ?>
</div></div>
<?= $this->render('../layouts/_page_size', ['dataProvider' => $dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin, number, integer} تا {end, number, integer} از {totalCount, number, integer} بخش',
    'emptyText' => $this->render('../layouts/_empty_state', [
        'icon' => 'ward',
        'message' => $term !== '' ? 'بخشی با این مشخصات پیدا نشد. عبارت دیگری را امتحان کنید.' : 'هنوز بخشی ثبت نشده است.',
        'action' => $term !== '' ? 'پاک کردن جست‌وجو' : 'ثبت اولین بخش',
        'route' => $term !== '' ? ['index'] : ['create'],
    ]),
    'columns' => [
        ['class' => 'app\components\PersianSerialColumn', 'header' => 'ردیف'],
        'name',
        'floor',
        ['attribute' => 'created_at', 'format' => 'datetime', 'contentOptions' => ['dir' => 'ltr']],
    ],
]) ?>
</div>
