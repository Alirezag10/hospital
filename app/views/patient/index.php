<?php
use yii\grid\GridView;
use yii\helpers\Html;
use app\components\Access;
$this->title = 'بیماران';
$this->params['breadcrumbs'][] = $this->title;
$hasFilters = (string) $searchModel->name !== '' || (string) $searchModel->national_code !== '' || (string) $searchModel->mobile !== '';
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p>بیمار را پیدا کنید و پرونده و سابقهٔ پذیرش او را ببینید.</p></div>
    <?php if (Access::allows('/patient/create')): ?><?= Html::a('ثبت بیمار جدید', ['create'], ['class' => 'btn btn-primary']) ?><?php endif; ?>
</div>
<?= $this->render('_search', ['model' => $searchModel]) ?>
<?= $this->render('../layouts/_page_size', ['dataProvider' => $dataProvider]) ?>
<div class="table-panel table-responsive">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
    'summary' => 'نمایش {begin, number, integer} تا {end, number, integer} از {totalCount, number, integer} بیمار',
    'emptyText' => $this->render('../layouts/_empty_state', [
        'icon' => 'patient',
        'message' => $hasFilters ? 'بیماری مطابق جست‌وجوی شما پیدا نشد. عبارت دیگری را امتحان کنید.' : 'هنوز بیماری ثبت نشده است.',
        'action' => $hasFilters ? 'پاک کردن جست‌وجو' : (Access::allows('/patient/create') ? 'ثبت اولین بیمار' : null),
        'route' => $hasFilters ? ['index'] : (Access::allows('/patient/create') ? ['create'] : null),
    ]),
    'columns' => [
        ['class' => 'app\components\PersianSerialColumn', 'header' => 'ردیف'],
        ['label' => 'بیمار', 'format' => 'raw', 'value' => function ($model) {
            return $this->render('/layouts/_patient_label', ['patient' => $model]);
        }],
        ['attribute' => 'mobile', 'contentOptions' => ['dir' => 'ltr']],
        ['label' => 'عملیات', 'format' => 'raw', 'value' => static function ($model) {
            $links = [Html::a('پرونده', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary'])];
            if (Access::allows('/patient/update')) {
                $links[] = Html::a('ویرایش', ['update', 'id'=>$model->id], ['class'=>'btn btn-sm btn-outline-secondary']);
            }
            if (Access::allows('/admission/create')) {
                $links[] = Html::a('ثبت پذیرش', ['/admission/create', 'patient_id' => $model->id], ['class' => 'btn btn-sm btn-primary']);
            }
            return implode(' ', $links);
        }],
    ],
]) ?>
</div>
