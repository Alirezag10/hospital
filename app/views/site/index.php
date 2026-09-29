<?php

use yii\helpers\Html;

/** @var int $patients */
/** @var int $activeAdmissions */
/** @var int $discharges */
/** @var yii\web\View $this */

$this->title = 'Hospital Dashboard';
?>

<div class="site-index">

    <h1>داشبورد بیمارستان</h1>

    <div class="row">

        <div class="col-md-4">
            <div class="card p-3">
                <h3>بیماران</h3>
                <h2><?= Html::encode($patients) ?></h2>

                <?= Html::a(
                    'مشاهده بیماران',
                    ['/patient/index'],
                    ['class' => 'btn btn-primary']
                ) ?>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card p-3">
                <h3>پذیرش فعال</h3>
                <h2><?= Html::encode($activeAdmissions) ?></h2>

                <?= Html::a(
                    'مشاهده پذیرش‌ها',
                    ['/admission/index'],
                    ['class' => 'btn btn-success']
                ) ?>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card p-3">
                <h3>ترخیص‌ها</h3>
                <h2><?= Html::encode($discharges) ?></h2>

                <?= Html::a(
                    'مشاهده ترخیص‌ها',
                    ['/discharge/index'],
                    ['class' => 'btn btn-warning']
                ) ?>
            </div>
        </div>

    </div>


    <hr>


    <h3>دسترسی سریع</h3>

    <?= Html::a(
        'ثبت بیمار جدید',
        ['/patient/create'],
        ['class' => 'btn btn-outline-primary']
    ) ?>

    <?= Html::a(
        'ثبت پذیرش جدید',
        ['/admission/create'],
        ['class' => 'btn btn-outline-success']
    ) ?>

    <?= Html::a(
        'ثبت خدمت',
        ['/admission-service/create'],
        ['class' => 'btn btn-outline-info']
    ) ?>

</div>