<?php
use yii\helpers\Html;

$this->title = 'تقویم پذیرش‌ها';
$this->params['breadcrumbs'][] = ['label' => 'پذیرش‌ها', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$cellCount = (int) (ceil(($startOffset + $daysInMonth) / 7) * 7);
?>
<div class="page-heading">
    <div><h1><?= Html::encode($this->title) ?></h1><p>پذیرش‌های ثبت‌شده در هر روز را ببینید.</p></div>
    <?= Html::a('فهرست پذیرش‌ها', ['index'], ['class' => 'btn btn-outline-primary']) ?>
</div>
<section class="calendar-panel" aria-label="تقویم پذیرش‌ها">
    <div class="calendar-toolbar">
        <?= Html::a('ماه قبل', ['calendar', 'month' => $previousMonth], ['class' => 'btn btn-outline-secondary']) ?>
        <h2 data-calendar-month="<?= Html::encode($firstDay->format('Y-m-01')) ?>"><?= Html::encode($firstDay->format('F Y')) ?></h2>
        <?= Html::a('ماه بعد', ['calendar', 'month' => $nextMonth], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <div class="calendar-grid" role="grid" aria-label="روزهای ماه">
        <?php foreach (['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'] as $weekday): ?>
            <div class="calendar-weekday" role="columnheader"><?= Html::encode($weekday) ?></div>
        <?php endforeach; ?>
        <?php for ($cell = 0; $cell < $cellCount; $cell++): ?>
            <?php $day = $cell - $startOffset + 1; $validDay = $day >= 1 && $day <= $daysInMonth; ?>
            <div class="calendar-day<?= $validDay ? '' : ' is-outside' ?>" role="gridcell"<?= $validDay ? ' aria-label="روز ' . $day . '"' : ' aria-hidden="true"' ?>>
                <?php if ($validDay): ?>
                    <?php $key = $firstDay->format('Y-m-') . str_pad((string) $day, 2, '0', STR_PAD_LEFT); ?>
                    <span class="calendar-day-number" data-calendar-date="<?= Html::encode($key) ?>"><?= $day ?></span>
                    <?php foreach (array_slice($admissionsByDay[$key] ?? [], 0, 3) as $admission): ?>
                        <?= Html::a(
                            Html::encode(($admission->patient->first_name ?? '') . ' ' . ($admission->patient->last_name ?? '')),
                            ['view', 'id' => $admission->id],
                            ['class' => 'calendar-event', 'title' => $admission->doctor_name . ' — ' . $admission->ward]
                        ) ?>
                    <?php endforeach; ?>
                    <?php $more = count($admissionsByDay[$key] ?? []) - 3; ?>
                    <?php if ($more > 0): ?><span class="calendar-more">و <?= $more ?> مورد دیگر</span><?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</section>
