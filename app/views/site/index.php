<?php
use yii\helpers\Html;
$this->title = 'داشبورد بیمارستان';
?>
<div class="site-index">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
    <div class="row g-3 mb-4">
        <?php foreach ([['بیماران', $patients], ['پذیرش‌های فعال', $activeAdmissions], ['ترخیص‌ها', $discharges]] as [$label, $count]): ?>
            <div class="col-md-4"><div class="card"><div class="card-body">
                <h2 class="h6"><?= Html::encode($label) ?></h2>
                <p class="display-6 mb-0"><?= number_format((int) $count) ?></p>
            </div></div></div>
        <?php endforeach; ?>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('ثبت بیمار', ['/patient/create'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ثبت پذیرش', ['/admission/create'], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a('ثبت خدمت', ['/admission-service/create'], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a('ثبت ترخیص', ['/discharge/create'], ['class' => 'btn btn-outline-primary']) ?>
    </div>
</div>
