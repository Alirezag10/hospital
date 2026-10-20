<?php
use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = 'مدیریت کاربران';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-heading"><div><h1><?= Html::encode($this->title) ?></h1><p>حساب کارکنان، نقش و وضعیت دسترسی آن‌ها را مدیریت کنید.</p></div>
    <?= Html::a('ساخت کاربر جدید', ['create'], ['class' => 'btn btn-primary']) ?></div>
<div class="card mb-3"><div class="card-body">
    <?= Html::beginForm(['index'], 'get', ['role' => 'search', 'aria-label' => 'جست‌وجوی کاربران']) ?>
    <div class="row g-3 align-items-end">
        <div class="col-lg-4"><?= Html::label('نام یا نام کاربری', 'user-query', ['class' => 'form-label']) ?><?= Html::textInput('q', $term, ['id' => 'user-query', 'class' => 'form-control', 'maxlength' => 100]) ?></div>
        <div class="col-lg-3"><?= Html::label('نقش', 'user-role-filter', ['class' => 'form-label']) ?><?= Html::dropDownList('role', $role, User::roleLabels(), ['id' => 'user-role-filter', 'class' => 'form-select', 'prompt' => 'همه نقش‌ها']) ?></div>
        <div class="col-lg-2"><?= Html::label('وضعیت', 'user-status-filter', ['class' => 'form-label']) ?><?= Html::dropDownList('status', $status, ['1' => 'فعال', '0' => 'غیرفعال'], ['id' => 'user-status-filter', 'class' => 'form-select', 'prompt' => 'همه']) ?></div>
        <div class="col-lg-3"><div class="form-actions mt-0"><?= Html::submitButton('جست‌وجو', ['class' => 'btn btn-primary']) ?><?= Html::a('پاک کردن', ['index'], ['class' => 'btn btn-outline-secondary']) ?></div></div>
    </div>
    <?= Html::endForm() ?>
</div></div>
<?= $this->render('/layouts/_page_size', ['dataProvider' => $dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider, 'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin, number, integer} تا {end, number, integer} از {totalCount, number, integer} کاربر',
    'emptyText' => 'کاربری مطابق این جست‌وجو پیدا نشد.',
    'columns' => [
        ['label' => 'کاربر', 'format' => 'raw', 'value' => static fn($user) => Html::tag('span',
            Html::tag('strong', Html::encode($user->full_name)) . Html::tag('small', Html::encode($user->username), ['dir' => 'ltr']), ['class' => 'patient-label'])],
        ['label' => 'نقش', 'value' => static fn($user) => $user->roleLabel],
        ['label' => 'پروفایل پزشک', 'value' => static fn($user) => $user->doctor?->name ?? '—'],
        ['label' => 'وضعیت', 'format' => 'raw', 'value' => static fn($user) => Html::tag('span', $user->is_active ? 'فعال' : 'غیرفعال',
            ['class' => 'status-badge ' . ($user->is_active ? 'status-admitted' : 'status-discharged')])],
        ['label' => 'تاریخ ساخت', 'attribute' => 'created_at', 'format' => 'datetime'],
        ['label' => 'عملیات', 'format' => 'raw', 'value' => static fn($user) => Html::a('ویرایش حساب', ['update', 'id' => $user->id], ['class' => 'btn btn-sm btn-outline-primary'])],
    ],
]) ?>
</div>
