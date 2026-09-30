<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
/** @var yii\web\View $this */
/** @var app\models\Service $model */
$canEdit = true;
$this->title = 'خدمت: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'خدمات', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="service-view" dir="rtl">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <div class="d-flex flex-wrap gap-2 mb-4">

        <?php if ($canEdit): ?>
        <?= Html::a('ویرایش', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a('حذف', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-outline-danger',
            'data' => ['confirm' => 'این رکورد حذف شود؟', 'method' => 'post'],
        ]) ?>
    <?php endif; ?>
        <?= Html::a('بازگشت به فهرست', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="card"><div class="card-body table-responsive">
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-striped table-bordered detail-view mb-0'],
            'attributes' => ['id', 'title',
  ['attribute' => 'price', 'label' => 'قیمت (تومان)', 'format' => ['decimal', 0]],
  ['attribute' => 'active', 'label' => 'وضعیت', 'value' => (int) $model->active === 1 ? 'فعال' : 'غیرفعال'],],
        ]) ?>
    </div></div>
</div>
