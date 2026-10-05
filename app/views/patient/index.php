<?php

use app\models\Patient;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\PatientSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'بیماران';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="patient-index" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-2"><?= Html::encode($this->title) ?></h1>
            <p class="text-muted mb-0">ثبت و جست‌وجوی اطلاعات بیماران</p>
        </div>
        <?= Html::a('ثبت بیمار جدید', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?= $this->render('_search', ['model' => $searchModel]) ?>

    <div class="card">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => 'نمایش {begin} تا {end} از {totalCount} بیمار',
                'emptyText' => 'بیماری مطابق جست‌وجوی شما پیدا نشد.',
                'layout' => "{summary}\n<div class=\"table-responsive mt-3\">{items}</div>\n{pager}",
                'tableOptions' => ['class' => 'table table-striped table-hover align-middle'],
                'pager' => [
                    'prevPageLabel' => 'قبلی',
                    'nextPageLabel' => 'بعدی',
                ],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn', 'header' => 'ردیف'],
                    'id',
                    'first_name',
                    'last_name',
                    ['attribute' => 'national_code', 'contentOptions' => ['dir' => 'ltr']],
                    ['attribute' => 'mobile', 'contentOptions' => ['dir' => 'ltr']],
                    [
                        'class' => ActionColumn::class,
                        'header' => 'عملیات',
                        'template' => '{admit} {view} {update} {delete}',
                        'contentOptions' => ['class' => 'text-nowrap'],
                        'buttons' => [
                            'admit' => static function ($url, Patient $model) {
                                return Html::a('ثبت پذیرش', ['/admission/create', 'patient_id' => $model->id], [
                                    'class' => 'btn btn-sm btn-primary',
                                    'aria-label' => 'ثبت پذیرش برای ' . $model->first_name . ' ' . $model->last_name,
                                ]);
                            },
                            'view' => static function ($url, Patient $model) {
                                return Html::a('مشاهده', $url, [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'aria-label' => 'مشاهده پرونده ' . $model->first_name . ' ' . $model->last_name,
                                ]);
                            },
                            'update' => static function ($url, Patient $model) {
                                return Html::a('ویرایش', $url, [
                                    'class' => 'btn btn-sm btn-outline-secondary',
                                    'aria-label' => 'ویرایش اطلاعات ' . $model->first_name . ' ' . $model->last_name,
                                ]);
                            },
                            'delete' => static function ($url, Patient $model) {
                                return Html::a('حذف', $url, [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'aria-label' => 'حذف بیمار ' . $model->first_name . ' ' . $model->last_name,
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => 'اطلاعات این بیمار حذف شود؟',
                                    ],
                                ]);
                            },
                        ],
                        'urlCreator' => static function ($action, Patient $model, $key, $index, $column) {
                            return Url::toRoute([$action, 'id' => $model->id]);
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
