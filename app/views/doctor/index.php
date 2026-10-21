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
    <?= $this->render('/layouts/_filters', ['formName' => '', 'filters' => ['q' => ['جست‌وجو', $term]]]) ?>
</div></div>
<?= $this->render('../layouts/_page_size', ['dataProvider' => $dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin, number, integer} تا {end, number, integer} از {totalCount, number, integer} پزشک',
    'emptyText' => $this->render('../layouts/_empty_state', [
        'icon' => 'doctor',
        'message' => $term !== '' ? 'پزشکی با این مشخصات پیدا نشد. عبارت دیگری را امتحان کنید.' : 'هنوز پزشکی ثبت نشده است.',
        'action' => $term !== '' ? 'پاک کردن جست‌وجو' : 'ثبت اولین پزشک',
        'route' => $term !== '' ? ['index'] : ['create'],
    ]),
    'columns' => [
        ['class' => 'app\components\PersianSerialColumn', 'header' => 'ردیف'],
        'name',
        'specialty',
        ['attribute' => 'active', 'format' => 'raw', 'value' => static fn($doctor) => Html::tag('span', $doctor->active ? 'فعال' : 'غیرفعال', ['class' => 'status-badge ' . ($doctor->active ? 'status-admitted' : 'status-discharged')])],
        ['label'=>'عملیات','format'=>'raw','value'=>static fn($doctor)=>Html::a('ویرایش',['update','id'=>$doctor->id],['class'=>'btn btn-sm btn-outline-secondary'])],
        ['attribute' => 'mobile', 'contentOptions' => ['dir' => 'ltr']],
        ['attribute' => 'created_at', 'format' => 'datetime', 'contentOptions' => ['dir' => 'ltr']],
        ['label' => 'حساب کاربری', 'format' => 'raw', 'value' => static fn($doctor) => $doctor->user
            ? Html::tag('span', ($doctor->user->is_active ? 'حساب فعال: ' : 'حساب غیرفعال: ') . Html::encode($doctor->user->username), ['class' => 'status-badge ' . ($doctor->user->is_active ? 'status-admitted' : 'status-discharged')])
            : Html::a('ساخت حساب پزشک', ['/user/create', 'doctor_id' => $doctor->id], ['class' => 'btn btn-sm btn-outline-primary'])],
    ],
]) ?>
</div>
