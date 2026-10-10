<?php
use yii\helpers\Html;
$this->title = 'داشبورد بیمارستان';
?>
<div class="site-index">
    <div class="dashboard-intro">
        <div>
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <p>از ثبت بیمار تا ترخیص، مراحل پرونده را از همین‌جا دنبال کنید.</p>
        </div>
        <svg class="dashboard-pulse" viewBox="0 0 140 60" aria-hidden="true" focusable="false"><path d="M2 32h28l10-12 13 28 16-42 16 38 10-12h43" pathLength="1" /></svg>
    </div>
    <section class="dashboard-stats" aria-label="آمار سامانه">
        <?php foreach ([['بیماران', $patients, 'patient'], ['پذیرش‌های فعال', $activeAdmissions, 'admission'], ['ترخیص امروز', $dischargesToday, 'discharge']] as [$label, $count, $icon]): ?>
            <div class="dashboard-stat">
                <span class="stat-label"><?= $this->render('/layouts/_icon', ['name' => $icon]) ?><?= Html::encode($label) ?></span>
                <strong><?= number_format((int) $count) ?></strong>
            </div>
        <?php endforeach; ?>
    </section>
    <h2 class="quick-title">دسترسی سریع</h2>
    <div class="row g-3">
        <?php foreach ([
            ['ثبت بیمار', 'مشخصات بیمار جدید را وارد کنید.', '/patient/create', 'patient'],
            ['ثبت پذیرش', 'بیمار، پزشک و بخش را مشخص کنید.', '/admission/create', 'admission'],
            ['ثبت خدمت', 'خدمات پرونده و تعداد را ثبت کنید.', '/admission-service/create', 'service'],
            ['ثبت ترخیص', 'هزینه‌ها را بررسی و ترخیص را ثبت کنید.', '/discharge/create', 'discharge'],
        ] as [$label, $description, $route, $icon]): ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <?= Html::a(
                    Html::tag('strong', $this->render('/layouts/_icon', ['name' => $icon]) . Html::encode($label))
                    . Html::tag('small', Html::encode($description)),
                    [$route], ['class' => 'card action-card']
                ) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
