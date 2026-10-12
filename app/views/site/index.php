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
        <?php foreach ([
            ['بیماران', $patients, 'patient', '/patient/index'],
            ['پذیرش‌های فعال', $activeAdmissions, 'admission', '/admission/index'],
            ['ترخیص امروز', $dischargesToday, 'discharge', '/discharge/create'],
        ] as [$label, $count, $icon, $route]): ?>
            <?= Html::beginTag('a', ['href' => \yii\helpers\Url::to([$route]), 'class' => 'dashboard-stat', 'aria-label' => $label . ': ' . $count . '، مشاهده']) ?>
                <span class="stat-label"><?= $this->render('/layouts/_icon', ['name' => $icon]) ?><?= Html::encode($label) ?></span>
                <strong><?= Yii::$app->formatter->asInteger((int) $count) ?></strong>
            <?= Html::endTag('a') ?>
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
    <section class="dashboard-recent" aria-labelledby="recent-admissions-title">
        <div class="section-heading">
            <div><h2 id="recent-admissions-title">آخرین پذیرش‌ها</h2><p>برای ادامهٔ کار، پروندهٔ بیمار را باز کنید.</p></div>
            <?= Html::a('همهٔ پذیرش‌ها', ['/admission/index'], ['class' => 'btn btn-outline-primary']) ?>
        </div>
        <?php if ($recentAdmissions): ?>
            <div class="table-panel table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th scope="col">بیمار</th><th scope="col">بخش</th><th scope="col">تاریخ پذیرش</th><th scope="col">وضعیت</th><th scope="col">عملیات</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentAdmissions as $admission): ?>
                        <tr>
                            <td><?= $this->render('/layouts/_patient_label', ['patient' => $admission->patient]) ?></td>
                            <td><?= Html::encode($admission->ward) ?></td>
                            <td dir="ltr"><?= Html::encode(Yii::$app->formatter->asDatetime($admission->admission_date)) ?></td>
                            <td><span class="status-badge <?= $admission->status === 'admitted' ? 'status-admitted' : 'status-discharged' ?>"><?= $admission->status === 'admitted' ? 'بستری' : 'ترخیص‌شده' ?></span></td>
                            <td><?= Html::a('مشاهده پرونده', ['/admission/view', 'id' => $admission->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?= $this->render('/layouts/_empty_state', ['icon' => 'admission', 'message' => 'هنوز پذیرشی ثبت نشده است.', 'action' => 'ثبت اولین پذیرش', 'route' => ['/admission/create']]) ?>
        <?php endif; ?>
    </section>
</div>
