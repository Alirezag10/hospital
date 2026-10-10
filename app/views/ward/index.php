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
</div></div>
<?= $this->render('../layouts/_page_size', ['dataProvider' => $dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin} تا {end} از {totalCount} بخش',
    'emptyText' => Html::tag('div',
        Html::tag('p', $term !== '' ? 'بخشی با این مشخصات پیدا نشد.' : 'هنوز بخشی ثبت نشده است.') .
        Html::a($term !== '' ? 'پاک کردن جست‌وجو' : 'ثبت اولین بخش', $term !== '' ? ['index'] : ['create'], ['class' => 'btn btn-primary']),
        ['class' => 'empty-state']
    ),
    'columns' => [
        ['class' => 'yii\grid\SerialColumn', 'header' => 'ردیف'],
        'name',
        'floor',
        ['attribute' => 'created_at', 'contentOptions' => ['dir' => 'ltr']],
    ],
]) ?>
</div>
