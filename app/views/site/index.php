<?php
use yii\helpers\Html;
$this->title = 'داشبورد بیمارستان';
?>
<div class="site-index">
    <div class="dashboard-intro">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <p>از ثبت بیمار تا ترخیص، مراحل پرونده را از همین‌جا دنبال کنید.</p>
    </div>
    <div class="row g-3 mb-4">
        <?php foreach ([['بیماران', $patients], ['پذیرش‌های فعال', $activeAdmissions], ['ترخیص‌ها', $discharges]] as [$label, $count]): ?>
            <div class="col-md-4"><div class="card stat-card"><div class="card-body">
                <h2 class="h6"><?= Html::encode($label) ?></h2>
                <p class="stat-number mb-0"><?= number_format((int) $count) ?></p>
            </div></div></div>
        <?php endforeach; ?>
    </div>
    <h2 class="quick-title">دسترسی سریع</h2>
    <div class="row g-3">
        <?php foreach ([
            ['۱', 'ثبت بیمار', 'مشخصات بیمار جدید را وارد کنید.', '/patient/create'],
            ['۲', 'ثبت پذیرش', 'بیمار، پزشک و بخش را مشخص کنید.', '/admission/create'],
            ['۳', 'ثبت خدمت', 'خدمات پرونده و تعداد را ثبت کنید.', '/admission-service/create'],
            ['۴', 'ثبت ترخیص', 'هزینه‌ها را بررسی و ترخیص را ثبت کنید.', '/discharge/create'],
        ] as [$step, $label, $description, $route]): ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <?= Html::a(
                    Html::tag('span', $step, ['class' => 'action-step', 'aria-hidden' => 'true'])
                    . Html::tag('strong', Html::encode($label))
                    . Html::tag('small', Html::encode($description)),
                    [$route], ['class' => 'card action-card']
                ) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
