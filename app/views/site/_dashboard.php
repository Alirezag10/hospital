<?php
use app\components\Access;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;

$isDoctor = Access::is(User::DOCTOR);
?>
    <section class="dashboard-stats" aria-label="آمار پنل">
        <?php foreach ($stats as [$label, $count, $icon, $route]): ?>
            <?= Html::beginTag('a', ['href' => Url::to($route), 'class' => 'dashboard-stat']) ?>
                <span class="stat-label">
                    <?= $this->render('/layouts/_icon', ['name' => $icon]) ?>
                    <?= Html::encode($label) ?>
                </span>
                <strong><?= Yii::$app->formatter->asInteger($count) ?></strong>
            <?= Html::endTag('a') ?>
        <?php endforeach; ?>
    </section>

    <?php if ($isDoctor): ?>
    <section class="dashboard-worklist" id="dashboard-worklist" aria-labelledby="dashboard-worklist-title">
        <div class="section-heading">
            <div>
                <h2 id="dashboard-worklist-title">پرونده‌های فعال من</h2>
                <p>پرونده‌های در انتظار تکمیل درمان ابتدا نمایش داده می‌شوند.</p>
            </div>
            <?= Html::a('مشاهده همه', ['/admission/index', 'AdmissionSearch' => ['status' => 'admitted']], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php if (empty($admissions)): ?>
            <div class="empty-state">
                <span class="empty-state-icon" aria-hidden="true">
                    <?= $this->render('/layouts/_icon', ['name' => 'admission']) ?>
                </span>
                <p>در حال حاضر پذیرش فعالی به شما اختصاص ندارد.</p>
            </div>
        <?php else: ?>
            <div class="table-panel table-responsive">
                <table class="table dashboard-cases">
                    <caption class="visually-hidden">پرونده‌های فعال من</caption>
                    <thead>
                        <tr>
                            <th scope="col">بیمار / کد ملی</th>
                            <th scope="col">بخش</th>
                            <th scope="col">تاریخ پذیرش</th>
                            <th scope="col">وضعیت پرونده</th>
                            <th scope="col">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admissions as $admission): ?>
                            <tr data-admission-id="<?= (int) $admission->id ?>">
                                <td>
                                    <?= Html::a(
                                        Html::encode($admission->patient === null
                                            ? 'بیمار نامشخص'
                                            : $admission->patient->first_name . ' ' . $admission->patient->last_name),
                                        ['/admission/view', 'id' => $admission->id],
                                        ['class' => 'dashboard-patient-name']
                                    ) ?>
                                    <small class="dashboard-cell-note" dir="ltr">
                                        <?= Html::encode($admission->patient?->national_code ?? 'نامشخص') ?>
                                    </small>
                                </td>
                                <td>
                                    <?= Html::encode($admission->ward) ?>
                                </td>
                                <td>
                                    <span dir="ltr"><?= Html::encode(Yii::$app->formatter->asDatetime(
                                        $admission->admission_date
                                    )) ?></span>
                                </td>
                                <td>
                                    <?php if ($admission->status === 'discharged'): ?>
                                        <span class="status-badge status-discharged">ترخیص‌شده</span>
                                    <?php elseif ($admission->ready_at !== null): ?>
                                        <span class="status-badge status-discharged">آمادهٔ ترخیص</span>
                                    <?php else: ?>
                                        <span class="status-badge status-admitted">در حال درمان</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                        <?= Html::a('مشاهده پرونده', ['/admission/view', 'id' => $admission->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="dashboard-list-summary">
                    نمایش <?= Yii::$app->formatter->asInteger(count($admissions)) ?>
                    از <?= Yii::$app->formatter->asInteger($total) ?> پرونده
                </p>
            </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
