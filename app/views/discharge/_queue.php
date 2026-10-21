<?php
use app\components\DashboardData;
use yii\helpers\Html;
?>
<section class="mb-4" aria-labelledby="ready-queue-title">
    <h2 class="h5" id="ready-queue-title">صف آمادهٔ ترخیص</h2>
    <p>حداکثر ۱۰ پرونده با طولانی‌ترین انتظار؛ برای سایر پرونده‌ها جست‌وجو کنید.</p>
    <?php if (empty($readyAdmissions)): ?>
        <div class="alert alert-info">هنوز پرونده‌ای با تأیید پایان درمان در صف ترخیص وجود ندارد.</div>
    <?php else: ?>
        <div class="table-panel table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">بیمار</th>
                        <th scope="col">بخش</th>
                        <th scope="col">زمان تأیید</th>
                        <th scope="col">مدت انتظار</th>
                        <th scope="col">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($readyAdmissions as $readyAdmission): ?>
                        <tr data-admission-id="<?= (int) $readyAdmission->id ?>">
                            <td><?= $this->render('/layouts/_patient_label', ['patient' => $readyAdmission->patient]) ?></td>
                            <td><?= Html::encode($readyAdmission->ward) ?></td>
                            <td><bdi><?= Html::encode(Yii::$app->formatter->asDatetime($readyAdmission->ready_at)) ?></bdi></td>
                            <td><?= Html::encode(DashboardData::waitingTime($readyAdmission->ready_at)) ?></td>
                            <td><?= Html::a('بررسی و ترخیص', ['/discharge/create', 'admission_id' => $readyAdmission->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="dashboard-list-summary">
                نمایش <?= Yii::$app->formatter->asInteger(count($readyAdmissions)) ?>
                از <?= Yii::$app->formatter->asInteger($readyTotal) ?> پرونده
            </p>
        </div>
    <?php endif; ?>
</section>
